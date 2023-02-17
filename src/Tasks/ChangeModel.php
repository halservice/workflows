<?php

namespace the42coders\Workflows\Tasks;

class ChangeModel extends Task
{
    public static array $fields = [
        'Model' => 'model',
        'Field' => 'field',
        'Value' => 'value',
    ];

    public static array $output = [
        'Output' => 'output',
    ];

    public static string $icon = '<i class="fas fa-database"></i>';

    /**
     * @return void
     */
    public function execute(): void
    {
        $model = $this->getData('model');
        $field = $this->getData('field');
        $value = $this->getData('value');

        $model->$field = $value;

        $this->setData('output', $model);
    }
}
