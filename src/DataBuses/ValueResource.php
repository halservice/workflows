<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

class ValueResource implements Resource
{
    /**
     * @param string $name
     * @param string $value
     * @param Model $model
     * @param DataBus $dataBus
     * @return string
     */
    public function getData(string $name, string $value, Model $model, DataBus $dataBus): string
    {
        return $value;
    }

    /**
     * @param Model $element
     * @param string|null $value
     * @param string|null $fieldName
     * @return array
     */
    public static function getValues(Model $element, ?string $value, ?string $fieldName): array
    {
        return [];
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
        return match ($operator) {
            'equal' => $dataBus->get($field) == $value,
            'not_equal' => $dataBus->get($field) != $value,
            default => true,
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
        if ($element->inputField($fieldName)) {
            return $element->inputField($fieldName)->render($element, $value, $fieldName);
        }

        return view('workflows::fields.text_field', [
            'value' => $value,
            'field' => $fieldName,
        ])->render();
    }
}
