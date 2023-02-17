<?php

namespace the42coders\Workflows\Tasks;

use Illuminate\Support\Facades\Blade;
use Symfony\Component\ErrorHandler\Error\FatalError;
use the42coders\Workflows\Fields\TextInputField;

class TextInput extends Task
{
    public static array $fields = [
        'Text' => 'text',
    ];

    public static array $output = [
        'TextOutput' => 'text_output',
    ];

    public static string $icon = '<i class="fas fa-font"></i>';

    /**
     * @return array
     */
    public function inputFields(): array
    {
        return [
            'text' => TextInputField::make(),
        ];
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $text = str_replace('&gt;', '>', $this->getData('text'));

        $php = Blade::compileString($text);
        $text = $this->render($php, [
            'model' => $this->model,
            'dataBus' => $this->dataBus,
        ]);

        $this->setData('text_output', $text);
    }

    /**
     * @param $__php
     * @param $__data
     * @return false|string
     */
    public function render($__php, $__data)
    {
        $obLevel = ob_get_level();
        ob_start();
        extract($__data, EXTR_SKIP);
        try {
            eval('?'.'>'.$__php);
        } catch (\Exception|\Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean();
    }
}
