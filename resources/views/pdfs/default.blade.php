<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transcript</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .page { padding: 30px 60px; page-break-after: always; }
        .page:last-of-type { page-break-after: auto; }
        h2 { margin-bottom: 4px; }
        .meta { margin-bottom: 16px; }
        .modules table { width: 100%; border-collapse: collapse; }
        .modules th, .modules td { border: 1px solid #ccc; padding: 6px 8px; font-size: 10pt; }
        .modules th { background: #f4f4f4; }
    </style>
</head>
<body>
@foreach ($transcripts as $transcript)
    @php
        $student = $transcript->student;
        $cgpa = $metrics[$transcript->id]['cgpa'] ?? null;
    @endphp
    <div class="page">
        <h2>Official Academic Transcript</h2>
        <div class="meta">
            <div><strong>Course:</strong> {{ $transcript->course->name }} ({{ $transcript->course->shortcode }})</div>
            <div><strong>Student:</strong> {{ $student->name }} | ID: {{ $student->student_identifier ?? 'N/A' }} | Serial: {{ $student->certificate_serial_number ?? 'N/A' }}</div>
            <div><strong>National ID:</strong> {{ $student->national_id ?? 'N/A' }}</div>
            <div><strong>Batch:</strong> {{ $student->batch_no ?? 'N/A' }} | Program: {{ $student->program ?? 'N/A' }} | Level: {{ $student->level ?? 'N/A' }}</div>
            <div><strong>Completed:</strong> {{ optional($transcript->completed_date)->format('Y-m-d') ?? 'Not set' }}</div>
            <div><strong>CGPA:</strong> {{ $cgpa !== null ? rtrim(rtrim(number_format($cgpa, 2, '.', ''), '0'), '.') : 'N/A' }}</div>
        </div>
        <div class="modules">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 120px;">Code</th>
                        <th>Module</th>
                        <th style="width: 70px;">Marks</th>
                        <th style="width: 70px;">Grade</th>
                        <th style="width: 60px;">GP</th>
                        <th style="width: 60px;">CP</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($transcript->moduleResults as $index => $module)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $module->code }}</td>
                        <td>{{ $module->name }}</td>
                        <td>{{ $module->marks }}</td>
                        <td>{{ $module->grade }}</td>
                        <td>{{ $module->gp }}</td>
                        <td>{{ $module->cp }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
</body>
</html>
