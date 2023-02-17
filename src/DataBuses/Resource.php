<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

interface Resource
{
    /**
     * @param string $name
     * @param string $value
     * @param Model $model
     * @param DataBus $dataBus
     * @return mixed
     */
    public function getData(string $name, string $value, Model $model, DataBus $dataBus);

    /**
     * @param Model $element
     * @param string|null $value
     * @param string|null $fieldName
     * @return mixed
     */
    public static function getValues(Model $element, ?string $value, ?string $fieldName);

    /**
     * @param Model $element
     * @param string|null $value
     * @param string $fieldName
     * @return string
     */
    public static function loadResourceIntelligence(Model $element, ?string $value, string $fieldName): string;
}
