<?php

namespace App\Support\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared "give me these rows as a file" helper used by every controller's
 * export/excel and export/pdf endpoints, plus the Reports summary export.
 *
 * Every export is built from the exact same three ingredients — a title,
 * a flat list of column headers, and a flat list of row arrays matching
 * those headers 1:1 — so a controller only has to decide *what* data goes
 * in, never *how* the file gets built.
 */
trait Exportable
{
    /**
     * Stream a styled .xlsx file back to the browser.
     *
     * @param  string  $title    Sheet title / heading row.
     * @param  array<int,string>  $headers
     * @param  iterable<int,array<int,mixed>>  $rows
     * @param  array<int,string>  $appliedFilters  Human-readable "Filter: value" lines shown above the table.
     */
    protected function exportExcel(string $title, array $headers, iterable $rows, string $filename, array $appliedFilters = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[^A-Za-z0-9 _-]/', '', $title) ?: 'Report', 0, 31));

        $lastCol = $this->columnLetter(count($headers));
        $row = 1;

        $sheet->setCellValue("A{$row}", $title);
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row++;

        $sheet->setCellValue("A{$row}", 'Generated: '.now()->format('Y-m-d H:i'));
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('667085');
        $row++;

        foreach ($appliedFilters as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('667085');
            $row++;
        }

        $row++; // blank spacer row

        $headerRow = $row;
        $sheet->fromArray($headers, null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('14919B');
        $row++;

        $dataStartRow = $row;
        $rowCount = 0;
        foreach ($rows as $dataRow) {
            $sheet->fromArray(array_values($dataRow), null, "A{$row}");
            $row++;
            $rowCount++;
        }

        if ($rowCount === 0) {
            $sheet->setCellValue("A{$row}", 'No records match the selected filters.');
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->getColor()->setRGB('667085');
            $row++;
        }

        $lastDataRow = max($row - 1, $headerRow);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastDataRow}")->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setWidth(22);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $this->withExtension($filename, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Render the shared exports.pdf-table Blade view to a downloadable PDF.
     *
     * @param  array<int,string>  $headers
     * @param  iterable<int,array<int,mixed>>  $rows
     * @param  array<int,string>  $appliedFilters
     */
    protected function exportPdf(string $title, array $headers, iterable $rows, string $filename, array $appliedFilters = [], string $orientation = 'portrait'): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('exports.pdf-table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'appliedFilters' => $appliedFilters,
            'generatedAt' => now(),
        ])->setPaper('a4', $orientation);

        return $pdf->download($this->withExtension($filename, 'pdf'));
    }

    private function withExtension(string $filename, string $extension): string
    {
        return str_ends_with($filename, ".{$extension}") ? $filename : "{$filename}.{$extension}";
    }

    private function columnLetter(int $count): string
    {
        $count = max($count, 1);
        $letter = '';
        while ($count > 0) {
            $count--;
            $letter = chr(65 + ($count % 26)).$letter;
            $count = intdiv($count, 26);
        }

        return $letter;
    }
}
