<?php

namespace the42coders\Workflows\Tasks;

class PregReplace extends Task
{
    public static array $fields = [
        'Pattern' => 'pattern',
        'Replacement' => 'replacement',
        'Subject' => 'subject',
    ];

    public static array $output = [
        'Preg Replace Output' => 'preg_replace_output',
    ];

    public static string $icon = '<i class="fas fa-shipping-fast"></i>';

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setData('preg_replace_output', preg_replace(
            $this->getData('pattern'),
            $this->getData('replacement'),
            $this->getData('subject')));
    }
}
