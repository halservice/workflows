<?php
namespace the42coders\Workflows\Concerns;

use Illuminate\Database\Eloquent\Model;
use the42coders\Workflows\DataBuses\DataBusResource;
use the42coders\Workflows\DataBuses\ModelResource;

trait RenderInput
{
    /**
     * @param Model $element
     * @param string $value
     * @param string $field
     * @return string
     */
    public function render(Model $element, string $value, string $field): string
    {
        $placeholders = [];

        $placeholders['data_bus'] = DataBusResource::getValues($element, $value, $field);
        foreach ($placeholders['data_bus'] as $dataBusKey => $dataBusValue) {
            $placeholders['data_bus'][$dataBusKey] = '$dataBus->get(\\\''.$dataBusValue.'\\\')';
        }

        $placeholders['model'] = ModelResource::getValues($element, $value, $field);
        foreach ($placeholders['model'] as $modelKey => $modelValue) {
            $placeholders['model'][$modelKey] = '$model->'.$modelValue;
        }

        return view('workflows::fields.text_input_field', [
            'field' => $field,
            'value' => $value,
            'placeholders' => $placeholders,
        ])->render();
    }
}