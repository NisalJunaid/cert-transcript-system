<?php

namespace App\Services;

use App\Models\Transcript;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TranscriptPdfGenerator
{
    public function generate(Collection $transcripts, string $template = 'default'): string
    {
        $pdf = new SimplePdf();

        foreach ($transcripts as $transcript) {
            if ($template === 'compact') {
                $this->renderCompact($pdf, $transcript);
            } elseif ($template === 'bachelors-single') {
                $this->renderBachelorsSingle($pdf, $transcript);
            } elseif ($template === 'certificate') {
                $this->renderCertificate($pdf, $transcript);
            } else {
                $this->renderDefault($pdf, $transcript);
            }
        }

        return $pdf->output();
    }

    private function renderDefault(SimplePdf $pdf, Transcript $transcript): void
    {
        $page = $pdf->addPage();
        $cursor = 60;

        $page->addTextFromTop(50, $cursor, 'Official Academic Transcript', 18);
        $cursor += 26;
        $page->addTextFromTop(50, $cursor, $transcript->course->name . ' [' . $transcript->course->shortcode . ']', 14);
        $cursor += 24;

        $student = $transcript->student;
        $infoLines = [
            'Student: ' . $student->name,
            'Student ID: ' . ($student->student_identifier ?? 'N/A'),
            'Certificate Serial: ' . ($student->certificate_serial_number ?? 'N/A'),
            'National ID: ' . ($student->national_id ?? 'N/A'),
            'Batch: ' . ($student->batch_no ?? 'N/A') . ' | Program: ' . ($student->program ?? 'N/A') . ' | Level: ' . ($student->level ?? 'N/A'),
            'Completed: ' . ($transcript->completed_date?->format('Y-m-d') ?? 'Not set'),
            'CGPA: ' . ($transcript->cgpa !== null ? number_format((float) $transcript->cgpa, 2) : 'N/A'),
            'Pass with Distinction: ' . ($transcript->pass_with_distinction ? 'Yes' : 'No'),
            'Dean\'s Award: ' . ($transcript->deans_award ? 'Yes' : 'No'),
        ];

        foreach ($infoLines as $line) {
            $page->addTextFromTop(50, $cursor, $line, 12);
            $cursor += 16;
        }

        $cursor += 8;
        $page->addTextFromTop(50, $cursor, 'Modules', 14);
        $cursor += 18;

        foreach ($transcript->moduleResults as $index => $module) {
            $moduleLine = ($index + 1) . '. ' . $module->name;
            if ($module->code) {
                $moduleLine .= ' [' . $module->code . ']';
            }

            $details = array_filter([
                $module->marks ? 'Marks: ' . $module->marks : null,
                $module->grade ? 'Grade: ' . $module->grade : null,
                $module->gp !== null ? 'GP: ' . $module->gp : null,
                $module->cp !== null ? 'CP: ' . $module->cp : null,
            ]);

            if (! empty($details)) {
                $moduleLine .= ' (' . implode(', ', $details) . ')';
            }

            $page->addTextFromTop(60, $cursor, $moduleLine, 11);
            $cursor += 14;

            if ($cursor > ($pdf->height() - 60)) {
                $page = $pdf->addPage();
                $cursor = 60;
            }
        }
    }

    private function renderCompact(SimplePdf $pdf, Transcript $transcript): void
    {
        $page = $pdf->addPage();
        $cursor = 50;

        $page->addTextFromTop(40, $cursor, 'Transcript Summary - ' . $transcript->course->shortcode, 16);
        $cursor += 20;

        $student = $transcript->student;
        $page->addTextFromTop(40, $cursor, $student->name . ' (' . ($student->student_identifier ?? 'N/A') . ')', 12);
        $cursor += 16;
        $page->addTextFromTop(40, $cursor, 'CGPA: ' . ($transcript->cgpa !== null ? number_format((float) $transcript->cgpa, 2) : 'N/A'), 12);
        $cursor += 16;

        $moduleLines = $transcript->moduleResults->map(function ($module, $index) {
            $parts = [($index + 1) . '. ' . $module->name];
            if ($module->grade) {
                $parts[] = 'Grade ' . $module->grade;
            }
            if ($module->marks) {
                $parts[] = $module->marks . ' pts';
            }

            return implode(' - ', $parts);
        });

        foreach ($moduleLines as $line) {
            $page->addTextFromTop(40, $cursor, Str::limit($line, 100), 11);
            $cursor += 14;

            if ($cursor > ($pdf->height() - 50)) {
                $page = $pdf->addPage();
                $cursor = 50;
            }
        }
    }

    private function renderBachelorsSingle(SimplePdf $pdf, Transcript $transcript): void
    {
        $modules = $transcript->moduleResults
            ->sortBy(['position', 'id'])
            ->values();

        $rowHeight = 16;
        $marginLeft = 80;
        $defaultTop = 140;
        $usableRows = (int) floor(($pdf->height() - $defaultTop - 80) / $rowHeight);
        $rowsPerColumn = max(1, $usableRows);
        $modulesPerPage = $rowsPerColumn * 2;

        $chunks = $modules->chunk($modulesPerPage);

        foreach ($chunks as $chunkIndex => $pageModules) {
            $page = $pdf->addPage();

            $headerBottom = $this->renderBachelorsHeader($page, $transcript, $marginLeft);
            $tableTop = max($defaultTop, $headerBottom + 20);

            $leftModules = $pageModules->take($rowsPerColumn);
            $rightModules = $pageModules->slice($rowsPerColumn, $rowsPerColumn);

            $this->renderBachelorsColumns($page, $leftModules, $tableTop, $marginLeft, $rowHeight);
            $this->renderBachelorsColumns($page, $rightModules, $tableTop, $marginLeft + 250, $rowHeight);

            $isLastChunk = ($chunkIndex === ($chunks->count() - 1));
            if ($isLastChunk) {
                $rowsUsed = max($leftModules->count(), $rightModules->count());
                $footerTop = $tableTop + ($rowsUsed + 2) * $rowHeight;
                $this->renderBachelorsFooter($page, $transcript, $footerTop, $marginLeft);
            }
        }
    }

    private function renderBachelorsHeader(SimplePdfPage $page, Transcript $transcript, float $marginLeft): float
    {
        $student = $transcript->student;

        $cursor = 70;
        $page->addTextFromTop($marginLeft, $cursor, 'Name: ' . $student->name, 12);
        $page->addTextFromTop($marginLeft + 200, $cursor, 'Passport No: ' . ($student->national_id ?? 'N/A'), 12);
        $page->addTextFromTop($marginLeft + 400, $cursor, 'Student ID: ' . ($student->student_identifier ?? 'N/A'), 12);

        $cursor += 18;
        $page->addTextFromTop($marginLeft, $cursor, 'Programme: ' . ($student->program ?? $student->level ?? 'N/A'), 12);
        $page->addTextFromTop($marginLeft + 200, $cursor, 'Completed on: ' . ($transcript->completed_date?->format('Y-m-d') ?? 'N/A'), 12);
        $cgpa = $transcript->cgpa !== null ? number_format((float) $transcript->cgpa, 2) : 'N/A';
        $page->addTextFromTop($marginLeft + 400, $cursor, 'CGPA: ' . $cgpa, 12);

        return $cursor;
    }

    private function renderBachelorsColumns(SimplePdfPage $page, Collection $modules, float $tableTop, float $columnLeft, int $rowHeight): void
    {
        $headerTop = $tableTop;
        $page->addTextFromTop($columnLeft, $headerTop, 'Module Code', 11);
        $page->addTextFromTop($columnLeft + 80, $headerTop, 'Module Title', 11);
        $page->addTextFromTop($columnLeft + 220, $headerTop, 'Grade', 11);
        $page->addTextFromTop($columnLeft + 250, $headerTop, 'GP', 11);
        $page->addTextFromTop($columnLeft + 280, $headerTop, 'CP', 11);

        $cursor = $tableTop + $rowHeight;
        foreach ($modules as $module) {
            $page->addTextFromTop($columnLeft, $cursor, $module->code ?: '-', 10);
            $page->addTextFromTop($columnLeft + 80, $cursor, $module->name ?: '-', 10);
            $page->addTextFromTop($columnLeft + 220, $cursor, $module->grade ?: '-', 10);

            $displayGp = '';
            if ($module->gp !== null) {
                $gpValue = is_numeric($module->gp) ? (float) $module->gp : $module->gp;
                if (! in_array($gpValue, [0, '0', 'Exempt'], true)) {
                    $displayGp = is_numeric($gpValue) ? rtrim(rtrim(number_format((float) $gpValue, 2, '.', ''), '0'), '.') : (string) $gpValue;
                }
            }
            $page->addTextFromTop($columnLeft + 250, $cursor, $displayGp ?: '-', 10);

            $displayCp = $module->cp !== null ? rtrim(rtrim((string) $module->cp, '0'), '.') : '-';
            $page->addTextFromTop($columnLeft + 280, $cursor, $displayCp ?: '-', 10);

            $cursor += $rowHeight;
        }
    }

    private function renderBachelorsFooter(SimplePdfPage $page, Transcript $transcript, float $footerTop, float $marginLeft): void
    {
        $totalCredit = $transcript->moduleResults
            ->sum(function ($module) {
                return is_numeric($module->cp) ? (float) $module->cp : 0;
            });

        $displayCredits = $totalCredit > 0 ? rtrim(rtrim(number_format($totalCredit, 2, '.', ''), '0'), '.') : '360';

        $page->addTextFromTop($marginLeft, $footerTop, 'Total credit points earned: ' . $displayCredits, 11);
        $page->addTextFromTop($marginLeft, $footerTop + 16, 'Programme Duration: 3 Years', 11);
        $page->addTextFromTop($marginLeft, $footerTop + 44, 'Serial no: ' . ($transcript->student->certificate_serial_number ?? 'N/A'), 8);
    }

    private function renderCertificate(SimplePdf $pdf, Transcript $transcript): void
    {
        $modules = $transcript->moduleResults
            ->sortBy(['position', 'id'])
            ->values();

        $rowHeight = 18;
        $marginLeft = 50;
        $headerTop = 90;
        $tableTop = 170;
        $footerOffset = 120;
        $rowsPerPage = max(1, (int) floor(($pdf->height() - $tableTop - $footerOffset) / $rowHeight));

        $chunks = $modules->chunk($rowsPerPage);
        if ($chunks->isEmpty()) {
            $chunks = collect([collect()]);
        }

        foreach ($chunks as $chunkIndex => $pageModules) {
            $page = $pdf->addPage();

            $this->renderCertificateHeader($page, $transcript, $marginLeft, $headerTop);
            $this->renderCertificateTable($page, $pageModules, $tableTop, $rowHeight, $marginLeft);

            $isLastPage = ($chunkIndex === ($chunks->count() - 1));
            if ($isLastPage) {
                $footerTop = $tableTop + ($pageModules->count() + 1) * $rowHeight + 20;
                $this->renderCertificateFooter($page, $transcript, $footerTop, $marginLeft);
            }
        }
    }

    private function renderCertificateHeader(SimplePdfPage $page, Transcript $transcript, float $marginLeft, float $top): void
    {
        $student = $transcript->student;
        $completed = $transcript->completed_date?->format('Y-m-d') ?? 'N/A';
        $cgpa = $transcript->cgpa !== null ? rtrim(rtrim(number_format((float) $transcript->cgpa, 2, '.', ''), '0'), '.') : 'N/A';
        $programme = $student->program ?? $student->level ?? 'N/A';

        $page->addTextFromTop($marginLeft, $top, 'Name: ' . $student->name, 12);
        $page->addTextFromTop($marginLeft + 200, $top, 'National ID: ' . ($student->national_id ?? 'N/A'), 12);
        $page->addTextFromTop($marginLeft + 400, $top, 'Student ID: ' . ($student->student_identifier ?? 'N/A'), 12);

        $page->addTextFromTop($marginLeft, $top + 24, 'Programme: ' . $programme, 12);
        $page->addTextFromTop($marginLeft + 200, $top + 24, 'Completed on: ' . $completed, 12);
        $page->addTextFromTop($marginLeft + 400, $top + 24, 'CGPA: ' . $cgpa, 12);
    }

    private function renderCertificateTable(SimplePdfPage $page, Collection $modules, float $tableTop, int $rowHeight, float $marginLeft): void
    {
        $headerY = $tableTop;
        $page->addTextFromTop($marginLeft, $headerY, 'Module Code', 11);
        $page->addTextFromTop($marginLeft + 120, $headerY, 'Module Title', 11);
        $page->addTextFromTop($marginLeft + 440, $headerY, 'Grade', 11);
        $page->addTextFromTop($marginLeft + 500, $headerY, 'GP', 11);
        $page->addTextFromTop($marginLeft + 540, $headerY, 'CP', 11);

        $cursor = $tableTop + $rowHeight;
        foreach ($modules as $module) {
            $page->addTextFromTop($marginLeft, $cursor, $module->code ?? '-', 10);
            $page->addTextFromTop($marginLeft + 120, $cursor, $module->name ?? '-', 10);
            $page->addTextFromTop($marginLeft + 440, $cursor, $module->grade ?? '-', 10);

            $page->addTextFromTop($marginLeft + 500, $cursor, $this->formatNumericField($module->gp), 10);
            $page->addTextFromTop($marginLeft + 540, $cursor, $this->formatNumericField($module->cp), 10);

            $cursor += $rowHeight;
        }
    }

    private function renderCertificateFooter(SimplePdfPage $page, Transcript $transcript, float $footerTop, float $marginLeft): void
    {
        $totalCredit = $transcript->moduleResults
            ->sum(function ($module) {
                return is_numeric($module->cp) ? (float) $module->cp : 0;
            });

        $displayCredits = $totalCredit > 0 ? rtrim(rtrim(number_format($totalCredit, 2, '.', ''), '0'), '.') : '40';

        $page->addTextFromTop($marginLeft, $footerTop, 'Total credit points earned: ' . $displayCredits, 11);
        $page->addTextFromTop($marginLeft, $footerTop + 18, 'Programme Duration: 6 months', 11);
        $page->addTextFromTop($marginLeft, $footerTop + 48, 'Serial no: ' . ($transcript->student->certificate_serial_number ?? 'N/A'), 8);
    }

    private function formatNumericField($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        }

        return (string) $value;
    }
}
