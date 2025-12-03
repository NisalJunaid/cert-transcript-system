<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Associate Degree Transcript</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .page { padding: 40px 60px; page-break-after: always; }
        .page:last-of-type { page-break-after: auto; }
        .placeholder { text-align: center; margin-top: 200px; color: #666; }
    </style>
</head>
<body>
@foreach ($transcripts as $transcript)
    <div class="page">
        <div class="placeholder">
            <h2>Associate Degree Transcript</h2>
            <p>This template has been reserved for level 6 programmes.</p>
            <p>Student: {{ $transcript->student->name }}</p>
            <p>Course: {{ $transcript->course->name }} ({{ $transcript->course->shortcode }})</p>
        </div>
    </div>
@endforeach
</body>
</html>
