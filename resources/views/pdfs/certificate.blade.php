<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<style>
    @font-face {
        font-family: 'Lucida Calligraphy';
        src: url('{{ storage_path("fonts/LucidaCalligraphy.ttf") }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    body {
        margin: 0;
        padding: 0;
        background: #fff;
        font-family: "Times New Roman", Times, serif;
        color: #000;
    }

    .certificate-container {
        margin: 0 auto;
        padding: 0;
        margin-top: 25px;
        padding-top: 0;
        position: relative;
        page-break-after: always;
    }

    .certificate-container:last-of-type {
        page-break-after: auto;
    }

    /* Center all text */
    .certificate-container * {
        text-align: center;
        line-height: 1.4;
    }

    .title {
        margin-top: 2.5in;
        font-size: 18px;
    }

    .name {
        font-size: 36px;
        font-weight: normal;
        font-family: "Lucida Calligraphy", cursive;
        margin: 0.5in 0 0;
    }

    .ids {
        font-size: 12pt;
        margin: 0.2in 0 0.5in;
        font-style: normal;
    }

    .fulfillment {
        font-size: 16pt;
        margin-top: 0.5in;
    }

    .degree {
        font-size: 16pt;
        font-weight: bold;
        margin: 0.25in 0 0.5in;
    }

    .award {
        margin-top:-5px;
        font-size: 16pt;
        font-weight: bold;
        margin: 0px;
        margin-top:-40px;
        padding: 0px;
    }

    .year {
        font-size: 16pt;
        margin: 0.5in 0 0.5in;
    }

    .under-seal {
        font-size: 14px;
        margin-top: 1in;
        margin-top:-10px;
    }

    .serial-number {
        font-size: 5pt;
        position: absolute;
        bottom: -20px;
        left: 50px;
        text-align: left;
        line-height: 1.2;
    }

</style>
<title>Certificate</title>
</head>
<body>
@foreach ($transcripts as $transcript)
    @php
        $student = $transcript->student;
        $cgpa = $metrics[$transcript->id]['cgpa'] ?? $transcript->cgpa;
        $awardYear = optional($transcript->completed_date)->format('Y') ?? now()->format('Y');
    @endphp
    <div class="certificate-container">
        <div class="title">This is to certify that</div>
        <div class="name" style="font-family: 'Lucida Calligraphy', serif;">{{ $student->name }}</div>
    <div class="ids">(National ID: {{ $student->national_id }}, Student ID: {{ $student->student_identifier ?? $student->student_id ?? 'N/A' }})</div>
        <div class="fulfillment">has successfully fulfilled the requirements of</div>
    <div class="degree">{{ $transcript->course->name ?? ($student->program ?? $student->level) }}</div>
        <div class="award">@if($cgpa !== null && $cgpa >= 3) (Pass with Distinction) @endif</div>
        <div class="year">In the year {{ $awardYear }}</div>
        <div class="under-seal">Given under the common seal of the College Council</div>
        <div class="serial-number">Serial No: {{ $student->certificate_serial_number ?? $student->serial_number ?? '' }}</div>
    </div>
@endforeach
</body>
</html>
