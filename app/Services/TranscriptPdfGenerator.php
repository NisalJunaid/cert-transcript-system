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
        $numericGps = $transcript->moduleResults
            ->pluck('gp')
            ->filter(static fn ($value) => is_numeric($value));

        if ($numericGps->isEmpty()) {
            return $transcript->cgpa !== null ? (float) $transcript->cgpa : null;
        }

        return $numericGps->sum() / $numericGps->count();
    }
}
