<?php

namespace the42coders\Workflows\Tasks;

use Illuminate\Support\Facades\Notification;
use the42coders\Workflows\Notifications\SlackNotification;

class SendSlackMessage extends Task
{
    public static array $fields = [
        'Channel/User' => 'channel',
        'Message' => 'message',
    ];

    public static array $output = [
        'Output' => 'output',
    ];

    public static string $icon = '<i class="fab fa-slack"></i>';


    /**
     * @return void
     */
    public function execute(): void
    {
        $channel = $this->getData('channel');
        $message = $this->getData('message');

        Notification::route('slack', env('WORKFLOW_SLACK_CHANNEL'))
            ->notify(new SlackNotification($channel, $message));
    }
}
