<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

class TimesheetPdfRenderer {
    private string $templatePath;

    public function __construct(?string $templatePath = null) {
        $this->templatePath = $templatePath ?? base_path('app/views/pdf/timesheet.php');
    }

    public function render(array $document): string {
        $this->validateDocument($document);

        if (!is_file($this->templatePath)) {
            throw new RuntimeException('The Timesheet PDF template could not be found.');
        }

        $html = $this->renderTemplate($document);

        $options = new Options();
        $options->setDefaultFont('DejaVu Sans');
        $options->setIsRemoteEnabled(false);
        $options->setChroot(base_path(''));

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        $pdfBytes = $dompdf->output();

        if ($pdfBytes === '' || !str_starts_with($pdfBytes, '%PDF-')) {
            throw new RuntimeException('The Timesheet PDF could not be rendered.');
        }

        return $pdfBytes;
    }

    private function renderTemplate(array $document): string {
        ob_start();

        try {
            require $this->templatePath;

            $html = ob_get_clean();

            if ($html === false || trim($html) === '') {
                throw new RuntimeException('The Timesheet PDF template produced no content.');
            }

            return $html;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private function validateDocument(array $document): void {
        $driver = $document['driver'] ?? null;

        if (!is_array($driver) || (int) ($driver['driver_id'] ?? 0) < 1 || trim((string) ($driver['first_name'] ?? '')) === '' || trim((string) ($driver['last_name'] ?? '')) === '') {
            throw new RuntimeException('The Timesheet PDF driver information is incomplete.');
        }

        if (trim((string) ($document['period_start'] ?? '')) === '' || trim((string) ($document['period_end'] ?? '')) === '' || (int) ($document['assignment_count'] ?? 0) < 1 || !isset($document['days']) || !is_array($document['days'])) {
            throw new RuntimeException('The Timesheet PDF data is incomplete.');
        }
    }
}

?>