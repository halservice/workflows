<?php

namespace the42coders\Workflows\Tasks;

use the42coders\Workflows\Fields\DropdownField;

class LoadModel extends Task
{
    public static array $fields = [
        'Model Class' => 'model_class',
        'Model Id' => 'model_id',
    ];

    public static array $output = [
        'Output' => 'output',
    ];

    public static string $icon = '<i class="fas fa-database"></i>';

    /**
     * @return array
     */
    public function inputFields(): array
    {
        return [
            'model_class' => DropdownField::make(config('workflows.task_settings.LoadModel.classes')),
        ];
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $modelClass = $this->getData('model_class');
        $modelId = $this->getData('model_id');

        $model = $modelClass::find($modelId);

        $this->setData('output', $model);
    }
}
