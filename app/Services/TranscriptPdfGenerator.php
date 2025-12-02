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
            'certificate' => 'pdfs.certificate',
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
                    'cgpa' => $cgpa,
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
            $gp = is_numeric($module->gp) ? (float) $module->gp : $this->gradePointFromGrade($module->grade);
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

        return $weightedPoints / $countedCredits;
    }

    private function gradePointFromGrade(?string $grade): ?float
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
            'HD' => 4.0,
            'DN' => 3.0,
            'CR' => 2.0,
            'PP', 'P' => 1.0,
            'FF', 'WF', 'F' => 0.0,
            default => null,
        };
    }
}
