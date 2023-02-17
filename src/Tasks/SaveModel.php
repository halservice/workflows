<?php

namespace the42coders\Workflows\Tasks;

class SaveModel extends Task
{
    public static array $fields = [
        'Model' => 'model',
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

        $model->save();

        $this->setData('output', $model);
    }
}
