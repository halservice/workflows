<?php

namespace the42coders\Workflows\Tasks;

use Illuminate\Support\Facades\Blade;
use the42coders\Workflows\Fields\TrixInputField;

class HtmlInput extends Task
{
    public static array $fields = [
        'Html' => 'html',
    ];

    public static array $output = [
        'HtmlOutput' => 'html_output',
    ];

    public static string $icon = '<i class="fas fa-code"></i>';

    /**
     * @return array
     */
    public function inputFields(): array
    {
        return [
            'html' => TrixInputField::make(),
        ];
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $html = str_replace('&gt;', '>', $this->getData('html'));

        $php = Blade::compileString($html);
        $html = $this->render($php, [
            'model' => $this->model,
            'dataBus' => $this->dataBus,
        ]);

        $this->setData('html_output', $html);
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
