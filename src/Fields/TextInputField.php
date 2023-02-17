<?php

namespace the42coders\Workflows\Fields;

use the42coders\Workflows\Concerns\RenderInput;

class TextInputField implements FieldInterface
{
    use RenderInput;

    public array $options;

    public function __construct()
    {
    }

    /**
     * @return static
     */
    public static function make(): self
    {
        return new self();
    }
}
