<?php

namespace the42coders\Workflows\Fields;

use Illuminate\Database\Eloquent\Model;

interface FieldInterface
{
    /**
     * @param Model $element
     * @param string $value
     * @param string $field
     * @return string
     */
    public function render(Model $element, string $value, string $field): string;
}
