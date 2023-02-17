<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

class ConfigResource implements Resource
{
    /**
     * @param string $name
     * @param string $value
     * @param Model $model
     * @param DataBus $dataBus
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getData(string $name, string $value, Model $model, DataBus $dataBus)
    {
        return config($value);
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
     * @param string|null $value
     * @param string $fieldName
     * @return string
     */
    public static function loadResourceIntelligence(Model $element, ?string $value, string $fieldName): string
    {
        if ($element->inputField($fieldName)) {
            return $element->inputField($fieldName)->render($fieldName, $value);
        }

        return view('workflows::fields.text_field', [
            'value' => $value,
            'field' => $fieldName,
        ])->render();
    }
}
