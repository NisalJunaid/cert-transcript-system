<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Transcript;
use App\Services\TranscriptPdfGenerator;
use Illuminate\Http\Request;

class TranscriptController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::orderBy('name')->get();
        $query = Transcript::with(['student', 'course', 'moduleResults' => function ($modules) {
            $modules->orderBy('position')->orderBy('id');
        }]);

        $search = $request->string('search')->trim();
        $courseId = $request->integer('course_id');
        $batch = $request->string('batch')->trim();
        $program = $request->string('program')->trim();
        $level = $request->string('level')->trim();

        if ($search->isNotEmpty()) {
            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('certificate_serial_number', 'like', "%{$search}%")
                    ->orWhere('student_identifier', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($batch->isNotEmpty()) {
            $query->whereHas('student', fn ($q) => $q->where('batch_no', 'like', "%{$batch}%"));
        }

        if ($program->isNotEmpty()) {
            $query->whereHas('student', fn ($q) => $q->where('program', 'like', "%{$program}%"));
        }

        if ($level->isNotEmpty()) {
            $query->whereHas('student', fn ($q) => $q->where('level', 'like', "%{$level}%"));
        }

        $transcripts = $query
            ->orderByDesc('completed_date')
            ->orderBy('student_id')
            ->paginate(20)
            ->withQueryString();

        return view('transcripts.index', [
            'transcripts' => $transcripts,
            'courses' => $courses,
            'filters' => [
                'search' => $search,
                'course_id' => $courseId,
                'batch' => $batch,
                'program' => $program,
                'level' => $level,
            ],
        ]);
    }

    public function pdf(Request $request, TranscriptPdfGenerator $generator)
    {
        $validated = $request->validate([
            'transcript_ids' => ['required', 'array', 'min:1'],
            'transcript_ids.*' => ['integer', 'exists:transcripts,id'],
            'template' => ['required', 'in:default,compact,bachelors-single'],
        ]);

        $transcripts = Transcript::with(['student', 'course', 'moduleResults' => function ($modules) {
            $modules->orderBy('position')->orderBy('id');
        }])
            ->whereIn('id', $validated['transcript_ids'])
            ->get();

        $pdfContent = $generator->generate($transcripts, $validated['template']);

        $filename = 'transcripts-' . $validated['template'] . '.pdf';

        return response()->streamDownload(
            static function () use ($pdfContent) {
                echo $pdfContent;
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}
