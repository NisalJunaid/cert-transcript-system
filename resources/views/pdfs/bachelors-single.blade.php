<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transcript - Bachelors Single</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .a4-container { margin-left: 80px; page-break-after: always; }
        .a4-container:last-of-type { page-break-after: auto; }
        .header { padding-top: 10%; }
        .first_table { border-collapse: collapse; width: 100%; margin: 30px 0; font-size: 11pt; }
        .module_area { display: flex; justify-content: space-between; height: 430px; }
        .module_area .module_table_container { width: 48%; float: left; }
        .module_table { border-collapse: collapse; width: 100%; margin: 0 auto; }
        .module_table tr { height: 20px; }
        .module_table td { text-align: left; padding: 5px; font-size: 8pt; }
        .footer { margin-top: 0px; }
        p { margin: 0; padding: 0; padding-top: 2px; }
    </style>
</head>
<body>
@foreach ($transcripts as $transcript)
    @php
        $student = $transcript->student;
        $modules = $transcript->moduleResults->sortBy([
            ['position', 'asc'],
            ['id', 'asc'],
        ]);
        $half = (int) ceil($modules->count() / 2);
        $firstHalf = $modules->slice(0, $half);
        $secondHalf = $modules->slice($half);
        $cgpa = $metrics[$transcript->id]['cgpa'] ?? null;
        $totalCredit = $metrics[$transcript->id]['total_credit'] ?? null;
    @endphp
    <div class="a4-container">
        <div class="header">
            <table class="first_table">
                <tr>
                    <td><b>Name</b></td>
                    <td style="width: 400px;">{{ $student->name }}</td>
                    <td style="width: 110px;"><b>Passport No</b></td>
                    <td>{{ $student->national_id }}</td>
                    <td style="width: 110px;"><b>Student ID</b></td>
                    <td>{{ $student->student_identifier }}</td>
                </tr>
                <tr>
                    <td style="width: 110px;"><b>Programme</b></td>
                    <td>{{ $student->program ?? $student->level }}</td>
                    <td><b>Completed on</b></td>
                    <td>{{ optional($transcript->completed_date)->format('Y-m-d') }}</td>
                    <td><b>CGPA</b></td>
                    <td>{{ $cgpa !== null ? rtrim(rtrim(number_format($cgpa, 2, '.', ''), '0'), '.') : 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="module_area">
            <div class="module_table_container">
                <table class="module_table">
                    <tr>
                        <td style="padding-left:0px; width: 60px;"><b>Module Code</b></td>
                        <td style="width: 240px;"><b>Module Title</b></td>
                        <td style="width: 20px;"><b>Grade</b></td>
                        <td style="width: 20px;"><b>GP</b></td>
                        <td><b>CP</b></td>
                    </tr>
                    @foreach ($firstHalf as $module)
                    <tr>
                        <td style="padding-left:0px; width: 60px;">{{ $module->code }}</td>
                        <td>{{ $module->name }}</td>
                        <td style="width:20px;">{{ $module->grade }}</td>
                        <td style="width:20px;">
                            @if($module->gp !== '0' && $module->gp !== 'Exempt')
                                {{ $module->gp }}
                            @endif
                        </td>
                        <td style="width:20px;">{{ $module->cp }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>

            <div class="module_table_container">
                <table class="module_table" style="padding-left:20px;">
                    <tr>
                        <td style="padding-left:0px; width: 60px;"><b>Module Code</b></td>
                        <td style="width: 240px;"><b>Module Title</b></td>
                        <td style="width: 20px;"><b>Grade</b></td>
                        <td style="width: 20px;"><b>GP</b></td>
                        <td><b>CP</b></td>
                    </tr>
                    @foreach ($secondHalf as $module)
                    <tr>
                        <td style="padding-left:0px; width: 60px;">{{ $module->code }}</td>
                        <td>{{ $module->name }}</td>
                        <td style="width:20px;">{{ $module->grade }}</td>
                        <td style="width:20px;">
                            @if($module->gp !== '0' && $module->gp !== 'Exempt')
                                {{ $module->gp }}
                            @endif
                        </td>
                        <td style="width:20px;">{{ $module->cp }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="footer">
            <p style="font-size: 10pt;">Total credit points earned: {{ $totalCredit && $totalCredit > 0 ? rtrim(rtrim(number_format($totalCredit, 2, '.', ''), '0'), '.') : '360' }}</p>
            <p style="font-size: 10pt;">Programme Duration: 3 Years</p>
            <p style="font-size: 4pt; margin-top:30px;">Serial no: {{ $student->certificate_serial_number }}</p>
        </div>
    </div>
@endforeach
</body>
</html>
