<?php

namespace the42coders\Workflows\Tasks;

class Execute extends Task
{
    public static array $fields = [
        'Command' => 'command',
    ];

    public static array $output = [
        'Command Output' => 'command_output',
    ];

    public static string $icon = '<i class="fas fa-terminal"></i>';

    /**
     * @return void
     */
    public function execute(): void
    {
        chdir(base_path());

        $this->setData('command_output', shell_exec($this->getData('command')));
    }
}
