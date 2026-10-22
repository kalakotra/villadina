<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\Model\Reservation;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use SilverStripe\i18n\i18n;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Proforma invoice / payment request for the amount currently due.
 * Not a fiscal invoice (fiskalizacija) — that is issued by the landlord's invoicing system.
 */
class ProformaPdf
{
    public function render(Reservation $r): string
    {
        return i18n::with_locale($r->Locale ?: i18n::get_locale(), function () use ($r): string {
            $config = SiteConfig::current_site_config();

            $html = (string) $r->customise([
                'SiteConfig' => $config,
                'IssueDate' => date('d.m.Y'),
                'PaymentReference' => $this->paymentReference($r),
            ])->renderWith('App\\Booking\\Pdf\\Proforma');

            $tempDir = TEMP_PATH . DIRECTORY_SEPARATOR . 'mpdf';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }

            $mpdf = new Mpdf([
                'tempDir' => $tempDir,
                'format' => 'A4',
                'margin_left' => 18,
                'margin_right' => 18,
                'margin_top' => 20,
                'margin_bottom' => 20,
                'default_font' => 'dejavusans',
            ]);
            $mpdf->SetTitle($this->filename($r));
            $mpdf->SetAuthor((string) $config->Title);
            $mpdf->WriteHTML($html);

            return $mpdf->Output('', Destination::STRING_RETURN);
        });
    }

    public function filename(Reservation $r): string
    {
        return 'Proforma-' . $r->Reference . '.pdf';
    }

    /**
     * Reference for the bank transfer, e.g. "VD-2027-0042 DEP".
     */
    public function paymentReference(Reservation $r): string
    {
        $kind = $r->dueNow()['kind'] ?? 'full';

        return $r->Reference . ' ' . match ($kind) {
            'deposit' => 'DEP',
            'balance' => 'BAL',
            default => 'FULL',
        };
    }
}
