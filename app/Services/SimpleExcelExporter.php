<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleExcelExporter
{
    /**
     * Export array data ke file .xls yang bisa dibuka langsung oleh Microsoft Excel / LibreOffice / Google Sheets.
     *
     * @param  string  $filename  tanpa ekstensi
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function export(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" '
                . 'xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta charset="UTF-8"></head><body><table border="1">';

            echo '<thead><tr>';
            foreach ($headers as $h) {
                echo '<th style="background:#0F2C4C;color:#fff;font-weight:bold;">' . e($h) . '</th>';
            }
            echo '</tr></thead><tbody>';

            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . e((string) $cell) . '</td>';
                }
                echo '</tr>';
            }

            echo '</tbody></table></body></html>';
        }, $filename . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xls"',
        ]);
    }
}
