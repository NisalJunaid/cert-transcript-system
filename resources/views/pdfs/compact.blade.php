<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transcript - Compact</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .page { padding: 30px 60px; page-break-after: always; }
        .page:last-of-type { page-break-after: auto; }
        h3 { margin-bottom: 10px; }
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
        <h3>Transcript Summary - {{ $transcript->course->shortcode }}</h3>
        <div><strong>{{ $student->name }}</strong> ({{ $student->student_identifier ?? 'N/A' }})</div>
        <div>CGPA: {{ $cgpa !== null ? rtrim(rtrim(number_format($cgpa, 2, '.', ''), '0'), '.') : 'N/A' }}</div>
        <div class="modules" style="margin-top: 12px;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Module</th>
                        <th style="width: 120px;">Grade</th>
                        <th style="width: 120px;">Marks</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($transcript->moduleResults as $index => $module)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $module->name }}</td>
                        <td>{{ $module->grade }}</td>
                        <td>{{ $module->marks }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
</body>
</html>
