<?php

namespace App\Http\Controllers;

use App\Http\Requests\TranscriptImportRequest;
use App\Models\Course;
use App\Models\ModuleResult;
use App\Models\Student;
use App\Models\Transcript;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TranscriptImportController extends Controller
{
    public function create()
    {
        $courses = Course::orderBy('name')->get();

        return view('transcripts.import', [
            'courses' => $courses,
        ]);
    }

    public function store(TranscriptImportRequest $request)
    {
        $course = Course::updateOrCreate(
            ['shortcode' => (string) $request->string('course_shortcode')->trim()],
            ['name' => (string) $request->string('course_name')->trim()]
        );

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $firstLine = fgets($handle) ?: '';
        $delimiter = $this->detectDelimiter($firstLine);
        $headers = $this->normalizeHeaders(str_getcsv($firstLine, $delimiter));

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $row = array_pad($row, count($headers), null);
            $rows[] = array_combine($headers, $row);
        }

        fclose($handle);

        $moduleIndices = $this->collectModuleIndices($headers);
        $imported = 0;

        foreach ($rows as $row) {
            $certificateSerial = $this->value($row, ['certificate_serial_number', 'certifciate_serial_number']);
            $studentIdentifier = $this->value($row, ['student_id', 'student_identifier']);
            $studentData = [
                'certificate_serial_number' => $certificateSerial,
                'student_identifier' => $studentIdentifier,
                'name' => $this->value($row, ['student_name', 'name']) ?? 'Unknown student',
                'national_id' => $this->value($row, ['student_national_id', 'national_id']),
                'batch_no' => $this->value($row, ['batch_no', 'batch']),
                'program' => $this->value($row, ['program']),
                'level' => $this->value($row, ['level']),
            ];

            if ($certificateSerial === null && $studentIdentifier === null) {
                $student = Student::create($studentData);
            } else {
                $student = Student::updateOrCreate(
                    [
                        'certificate_serial_number' => $certificateSerial,
                        'student_identifier' => $studentIdentifier,
                    ],
                    $studentData
                );
            }

            $transcript = Transcript::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                ],
                [
                    'cgpa' => $this->toNullableNumber($this->value($row, ['cgpa'])),
                    'pass_with_distinction' => $this->toBoolean($this->value($row, ['pass_with_distinction'])),
                    'deans_award' => $this->toBoolean($this->value($row, ['deans_award'])),
                    'completed_date' => $this->toDate($this->value($row, ['completed_date'])),
                ]
            );

            $transcript->moduleResults()->delete();

            foreach ($moduleIndices as $index) {
                $name = $this->value($row, ["module_name_{$index}"]);
                $code = $this->value($row, ["module_code_{$index}"]);
                $marks = $this->value($row, ["marks_{$index}", "mark_{$index}"]);
                $grade = $this->value($row, ["grade_{$index}"]);
                $gp = $this->toNullableNumber($this->value($row, ["gp{$index}", "gp_{$index}"]));
                $cp = $this->toNullableNumber($this->value($row, ["cp{$index}", "cp_{$index}"]));

                if ($name === null && $code === null && $marks === null && $grade === null && $gp === null && $cp === null) {
                    continue;
                }

                ModuleResult::create([
                    'transcript_id' => $transcript->id,
                    'name' => $name ?? 'Module',
                    'code' => $code,
                    'marks' => $marks,
                    'grade' => $grade,
                    'gp' => $gp,
                    'cp' => $cp,
                    'position' => $index,
                ]);
            }

            $imported++;
        }

        $message = $imported === 1 ? '1 transcript imported' : "{$imported} transcripts imported";

        return redirect()
            ->route('transcripts.index')
            ->with('status', "$message for {$course->name} ({$course->shortcode}).");
    }

    private function detectDelimiter(string $line): string
    {
        $delimiters = [",", ";", "\t"];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($counts);

        return key($counts);
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $normalized = preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim((string) $header)));
            $normalized = trim($normalized, '_');

            return $normalized;
        }, $headers);
    }

    private function collectModuleIndices(array $headers): array
    {
        $indices = [];

        foreach ($headers as $header) {
            if (preg_match('/module_name_(\d+)/', $header, $matches)) {
                $indices[] = (int) $matches[1];
            }
        }

        sort($indices);

        return $indices;
    }

    private function value(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    private function toBoolean($value): bool
    {
        if ($value === null) {
            return false;
        }

        $value = Str::of((string) $value)->trim()->lower();

        return in_array($value, ['1', 'yes', 'y', 'true'], true);
    }

    private function toNullableNumber($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $clean = Str::of((string) $value)->replace(',', '.')->trim();

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function toDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);

        if ($timestamp === false) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp)->toDateString();
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
