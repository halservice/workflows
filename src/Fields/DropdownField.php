<?php

namespace the42coders\Workflows\Fields;

use Illuminate\Database\Eloquent\Model;

class DropdownField implements FieldInterface
{
    public array $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $options
     * @return DropdownField
     */
    public static function make(array $options): self
    {
        return new self($options);
    }

    /**
     * @param Model $element
     * @param string $value
     * @param string $field
     * @return string
     */
    public function render(Model $element, string $value, string $field): string
    {
        return view('workflows::fields.dropdown_field', [
            'field' => $field,
            'value' => $value,
            'options' => $this->options,
        ])->render();
    }
}
