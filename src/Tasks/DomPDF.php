<?php

namespace the42coders\Workflows\Tasks;

use Barryvdh\DomPDF\Facade as PDF;

class DomPDF extends Task
{
    public static array $fields = [
        'Html' => 'html',
    ];

    public static array $output = [
        'PDFFile' => 'pdf_file',
    ];

    public static string $icon = '<i class="fa fa-file-pdf"></i>';

    public function execute(): void
    {
        $pdf = PDF::loadHTML($this->getData('html'));
        $this->setDataArray('pdf_file', $pdf->output());
    }
}
