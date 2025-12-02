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
}
