<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Transcript</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .a4-container {
            margin-left:50px;
            page-break-after: always;
        }
        .a4-container:last-of-type {
            page-break-after: auto;
        }
        .header {
            padding-top: 12%;
        }
        .first_table {
            border-collapse: collapse;
            width: 100%;
            margin: 30px;
        }
        .module_area {
            height: 350px;
        }
        .module_table {
            justify-content: center;
            align-items: center;
            border-collapse: collapse;
            margin: 0 auto;
        }
        .footer {
            margin-top: 5;
            margin-left:30px;
        }
        p {
            margin: 0;
            padding: 0;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    @foreach ($transcripts as $transcript)
        @php
            $student = $transcript->student;
            $cgpa = $metrics[$transcript->id]['cgpa'] ?? $transcript->cgpa;
            $totalcredit = $metrics[$transcript->id]['total_credit'] ?? null;
        @endphp
        <div class="a4-container">
            <div class="header">
                <table class="first_table">
                    <tr>
                        <td><b>Name</b></td>
                        <td>{{ $student->name }}</td>
                        <td style="width: 110px;"><b>National ID</b></td>
                        <td>{{ $student->national_id }}</td>
                        <td style="width: 110px;"><b>Student ID</b></td>
                        <td>{{ $student->student_identifier ?? $student->student_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 110px;"><b>Programme</b></td>
                        <td>{{ $transcript->course->name ?? $student->program ?? $student->level }}</td>
                        <td><b>Completed on</b></td>
                        <td>{{ optional($transcript->completed_date)->format('Y-m-d') ?? 'N/A' }}</td>
                        <td><b>CGPA</b></td>
                        <td>{{ $cgpa !== null ? number_format($cgpa, 2, '.', '') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="module_area">
                <table class="module_table">
                    <tr style="height: 30px;">
                        <td style="padding-left:20px; width: 120px;"><b>Module Code</b></td>
                        <td style="width: 520px;"><b>Module Title</b></td>
                        <td style="width: 100px;"><b>Grade</b></td>
                        <td style="width: 65px;"><b>GP</b></td>
                        <td><b>CP</b></td>
                    </tr>
                    @foreach ($transcript->moduleResults as $module)
                        <tr style="height: 25px;">
                            <td style="padding-left:20px; width: 100px;">{{ $module->code }}</td>
                            <td>{{ $module->name }}</td>
                            <td>{{ $module->grade }}</td>
                            <td>{{ $module->gp !== null ? (int) $module->gp : '' }}</td>
                            <td>{{ $module->cp !== null ? rtrim(rtrim(number_format($module->cp, 2, '.', ''), '0'), '.') : '' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="footer">
                <p>Total credit points earned: {{ $totalcredit !== null ? rtrim(rtrim(number_format($totalcredit, 2, '.', ''), '0'), '.') : '40' }}</p>
                <p>Programme Duration: 6 months</p>
                <p style="font-size: xx-small; margin-top:45px;">Serial no: {{ $student->certificate_serial_number ?? $student->serial_number ?? '' }}</p>
            </div>
        </div>
    @endforeach
</body>
</html>
