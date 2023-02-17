<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

class DataBusResource implements Resource
{
    /**
     * @param string $name
     * @param string $value
     * @param Model $model
     * @param DataBus $dataBus
     * @return mixed
     */
    public function getData(string $name, string $value, Model $model, DataBus $dataBus): mixed
    {
        return $dataBus->data[$dataBus->data[$value]];
    }

    /**
     * @param Model $element
     * @param DataBus $dataBus
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return bool
     */
    public static function checkCondition(Model $element, DataBus $dataBus, string $field, string $operator, string $value)
    {
        switch ($operator) {
            case 'equal':
                return $dataBus->data[$dataBus->data[$field]] == $value;
            case 'not_equal':
                return $dataBus->data[$dataBus->data[$field]] != $value;
            default:
                return true;
        }
    }

    /**
     * @param Model $element
     * @param string|null $value
     * @param string|null $fieldName
     * @return mixed
     */
    public static function getValues(Model $element, ?string $value, ?string $fieldName)
    {
        return $element->getParentDataBusKeys();
    }

    /**
     * @param Model $element
     * @param string|null $value
     * @param string $fieldName
     * @return string
     */
    public static function loadResourceIntelligence(Model $element, ?string $value, string $fieldName): string
    {
        $fields = self::getValues($element, $value, $fieldName);

        return view('workflows::fields.data_bus_resource_field', [
            'fields' => $fields,
            'value' => $value,
            'field' => $fieldName,
        ])->render();
    }
}
