<?php

namespace the42coders\Workflows\Tasks;

use Illuminate\Support\Facades\Mail;

class SendMail extends Task
{
    public static array $fields = [
        'Subject' => 'subject',
        'Recipients' => 'recipients',
        'Sender' => 'sender',
        'Content' => 'content',
        'Files' => 'files',
    ];

    public static string $icon = '<i class="far fa-envelope"></i>';

    /**
     * @return void
     */
    public function execute(): void
    {
        $dataBus = $this->dataBus;

        Mail::html($dataBus->get('content'), function ($message) use ($dataBus) {
            $message->subject($dataBus->get('subject'))
                ->to($dataBus->get('recipients'))
                ->from($dataBus->get('sender'));
            $counter = 1;
            if (is_array($dataBus->get('files'))) {
                foreach ($dataBus->get('files') as $file) {
                    $message->attachData($file, 'Datei_'.$counter);
                    $counter++;
                }
            }
        });
    }
}
