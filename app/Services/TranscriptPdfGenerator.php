<?php

namespace App\Services;

use App\Models\Transcript;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class TranscriptPdfGenerator
{
    public function generate(Collection $transcripts, string $template = 'default'): string
    {
        $view = match ($template) {
            'compact' => 'pdfs.compact',
            'bachelors-single' => 'pdfs.bachelors-single',
            'certificate-transcript' => 'pdfs.certificate-transcript',
            'certificate-award' => 'pdfs.certificate',
            default => 'pdfs.default',
        };

        $metrics = $this->buildMetrics($transcripts);

        $html = view($view, [
            'transcripts' => $transcripts,
            'metrics' => $metrics,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->output();
    }

    private function buildMetrics(Collection $transcripts): array
    {
        return $transcripts->mapWithKeys(function (Transcript $transcript) {
            $cgpa = $this->calculateCgpa($transcript);
            $totalCredit = $transcript->moduleResults
                ->sum(function ($module) {
                    return is_numeric($module->cp) ? (float) $module->cp : 0;
                });

            return [
                $transcript->id => [
                    'cgpa' => $cgpa !== null ? round($cgpa, 2) : null,
                    'total_credit' => $totalCredit,
                ],
            ];
        })->all();
    }

    private function calculateCgpa(Transcript $transcript): ?float
    {
        $weightedPoints = 0;
        $countedCredits = 0;

        foreach ($transcript->moduleResults as $module) {
            $gp = is_numeric($module->gp) ? (int) $module->gp : $this->gradePointFromGrade($module->grade);
            $cp = is_numeric($module->cp) ? (float) $module->cp : null;

            if ($gp === null || $cp === null || $cp <= 0) {
                continue;
            }

            $weightedPoints += $gp * $cp;
            $countedCredits += $cp;
        }

        if ($countedCredits === 0) {
            return $transcript->cgpa !== null ? (float) $transcript->cgpa : null;
        }

        return round($weightedPoints / $countedCredits, 2);
    }

    private function gradePointFromGrade(?string $grade): ?int
    {
        if ($grade === null) {
            return null;
        }

        $normalized = strtoupper(trim($grade));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['I', 'DF', 'S', 'U'], true)) {
            return null;
        }

        return match ($normalized) {
            'HD' => 4,
            'DN' => 3,
            'CR' => 2,
            'PP', 'P' => 1,
            'FF', 'WF', 'F' => 0,
            default => null,
        };
    }
}
