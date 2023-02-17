<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use function PHPUnit\Framework\matches;

class ModelResource implements Resource
{
    /**
     * @param string $name
     * @param string $value
     * @param Model $model
     * @param DataBus $dataBus
     * @return mixed
     */
    public function getData(string $name, string $value, Model $model, DataBus $dataBus)
    {
        return $model->{$value};
    }

    /**
     * @param Model $element
     * @param string|null $value
     * @param string|null $fieldName
     * @return array
     */
    public static function getValues(Model $element, ?string $value, ?string $fieldName)
    {
        $classes = [];
        foreach ($element->workflow->triggers as $trigger) {
            if (isset($trigger->data_fields['class']['value'])) {
                $classes[] = $trigger->data_fields['class']['value'];
            }
        }

        $variables = [];
        foreach ($classes as $class) {
            $model = new $class;
            foreach (Schema::getColumnListing($model->getTable()) as $item) {
                $variables[$class.'->'.$item] = $item;
            }
        }

        return $variables;
    }

    /**
     * @param Model $element
     * @param DataBus $dataBus
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return bool
     */
    public static function checkCondition(Model $element, DataBus $dataBus, string $field, string $operator, string $value): bool
    {
        return match($operator) {
            'equal' => $element->{$field} == $value,
            'not_equal' => $element->{$field} != $value,
            default => true
        };
    }

    /**
     * @param Model $element
     * @param string|null $value
     * @param string $fieldName
     * @return string
     */
    public static function loadResourceIntelligence(Model $element, ?string $value, string $fieldName): string
    {
        $variables = self::getValues($element, $value, $fieldName);

        return view('workflows::fields.data_bus_resource_field', [
            'fields' => $variables,
            'value' => $value,
            'field' => $fieldName,
        ])->render();
    }
}
