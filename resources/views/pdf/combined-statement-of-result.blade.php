<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Combined Statement of Result</title>

    <style>
        @font-face{
            font-family:"Old English Text MT";
            src:url("{{ public_path('assets/fonts/oldenglishtextmt.ttf') }}") format("truetype");
        }

        @page{
            size:A4 landscape;
            margin:8mm;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:"Times New Roman",serif;
            color:#000;
            background:#fff;
            font-size:12px;
        }

        .certificate{
            position:relative;
            padding:10px;
            min-height:186mm;
        }

        .watermark{
            position:absolute;
            width:390px;
            left:50%;
            top:52%;
            margin-left:-195px;
            margin-top:-195px;
            opacity:.05;
            z-index:1;
        }

        .content{
            position:relative;
            z-index:2;
        }

        .letterhead-wrap{
            width:100%;
            text-align:center;
            margin-bottom:4px;
        }

        .letterhead{
            display:inline-block;
            height:150px;
            width:auto;
            max-width:100%;
        }

        .title{
            position:relative;
            text-align:center;
            margin-bottom:10px;
        }

        .issue-date{
            position:absolute;
            right:5px;
            top:2px;
            font-size:16px;
            font-weight:bold;
        }

        .main-title{
            font-size:30px;
            font-weight:900;
            text-decoration:underline;
        }

        .session{
            font-size:18px;
            font-weight:bold;
            margin-top:3px;
        }

        .exam{
            font-size:17px;
            font-weight:bold;
            margin-top:3px;
        }

        table.result-table{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:10px;
        }

        .result-table th,
        .result-table td{
            border:1px solid #333;
            padding:5px 4px;
            vertical-align:middle;
        }

        .result-table thead th{
            background:#E8EEF8;
            text-align:center;
            font-weight:bold;
        }

        .group{
            background:#D9E3F3;
            font-size:11px;
        }

        .name{
            text-align:left;
            font-weight:bold;
            font-size:11px;
        }

        .subject{
            text-align:center;
            font-weight:bold;
        }

        .grade,
        .point{
            text-align:center;
            font-weight:bold;
            font-size:11px;
        }

        .total{
            text-align:center;
            font-size:16px;
            font-weight:bold;
        }

        .remark{
            text-align:center;
            font-size:12px;
            font-weight:bold;
        }

        .key{
            margin-top:16px;
            font-size:13px;
            font-weight:bold;
            line-height:1.55;
        }

        table.footer{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }

        .footer td{
            vertical-align:bottom;
        }

        .stamp-wrap{
            position:relative;
            width:330px;
            height:95px;
        }

        .stamp{
            position:absolute;
            left:0;
            bottom:0;
            width:150px;
        }

        .signature{
            position:absolute;
            left:160px;
            bottom:22px;
            width:120px;
        }

        .director{
            position:absolute;
            left:160px;
            bottom:0;
            font-size:16px;
        }

        .director span{
            display:block;
            font-size:14px;
        }

        .notice{
            border:1px solid #666;
            border-radius:8px;
            padding:12px;
            width:180px;
            text-align:center;
            font-size:12px;
            line-height:1.5;
        }
    </style>

</head>
<body>

<div class="certificate">

    <img src="{{ asset('assets/jupeb/watermark.png') }}" class="watermark">

    <div class="content">
        <div class="letterhead-wrap">
            <img
                src="{{ $settings->get('letterhead_image')
            ? Storage::path($settings->get('letterhead_image'))
            : asset('assets/jupeb/letterhead_landscape.png') }}"
                class="letterhead"
                alt="JUPEB Letterhead"
            >
        </div>

        <div class="title">

            <div class="issue-date">
                Date: {{ $issueDate }}
            </div>

            <div class="main-title">
                COMBINED STATEMENT OF RESULT
            </div>

            <div class="session">
                {{ $academicSession ?? '2025/2026' }} ACADEMIC SESSION
            </div>

            <div class="exam">
                2025 JUPEB EXAMINATION (A-LEVEL EQUIVALENT)
            </div>

        </div>

        <table class="result-table">

            <thead>

            <tr>

                <th rowspan="2" width="3%">S/N</th>

                <th colspan="3" class="group">STUDENT INFORMATION</th>

                <th colspan="9" class="group">SUBJECTS</th>

                <th colspan="2" class="group">TOTAL</th>

            </tr>

            <tr>

                <th width="15%">NAME (SURNAME FIRST)</th>

                <th width="10%">FOUNDATION NO.</th>

                <th width="10%">EXAMINATION NO.</th>

                <th width="11%">SUBJECT 1</th>
                <th width="4%">GRADE</th>
                <th width="4%">POINT</th>

                <th width="11%">SUBJECT 2</th>
                <th width="4%">GRADE</th>
                <th width="4%">POINT</th>

                <th width="10%">SUBJECT 3</th>
                <th width="4%">GRADE</th>
                <th width="4%">POINT</th>

                <th width="8%">GRADE POINT<br>(OUT OF 16)</th>

                <th width="7%">REMARKS</th>

            </tr>

            </thead>

            <tbody>

            @forelse($results as $index=>$row)

                @php
                    $student=$row['student'];
                    $result=$row['result'];
                    $isPass=$result->total_point>=8;
                @endphp

                <tr>

                    <td>{{ $index+1 }}</td>

                    <td class="name">
                        {{ $student->lastNameFirst() }}
                    </td>

                    <td>
                        {{ $student->foundation_number }}
                    </td>

                    <td>
                        {{ $student->examination_number ?? '—' }}
                    </td>

                    <td class="subject">
                        {{ strtoupper($result->subject_one) }}
                    </td>

                    <td class="grade">
                        {{ $result->grade_one->value }}
                    </td>

                    <td class="point">
                        {{ $result->point_one }}
                    </td>

                    <td class="subject">
                        {{ strtoupper($result->subject_two) }}
                    </td>

                    <td class="grade">
                        {{ $result->grade_two->value }}
                    </td>

                    <td class="point">
                        {{ $result->point_two }}
                    </td>

                    <td class="subject">
                        {{ strtoupper($result->subject_three) }}
                    </td>

                    <td class="grade">
                        {{ $result->grade_three->value }}
                    </td>

                    <td class="point">
                        {{ $result->point_three }}
                    </td>

                    <td class="total">
                        {{ $result->total_point }}/16
                    </td>

                    <td class="remark">
                        {{ $isPass?'PASS':'FAIL' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="15" style="text-align:center;padding:20px;">
                        No results available.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

        <div class="key">

            Key to Grade:

            A = 70–100 (5 Points); B = 60–69 (4 Points); C = 50–59 (3 Points); D = 45–49 (2 Points)

            E = 40–45 (1 Point); F(Fail) = 0–39 (0 Point)

            X = Absent; Q = Cancelled; W = Withheld

            One Point added if all 3 subjects passed.

        </div>

        <table class="footer">

            <tr>

                <td width="70%">

                    <div class="stamp-wrap">

                        <img src="{{ $stamp }}" class="stamp">

                        <img src="{{ $signature }}" class="signature">

                        <div class="director">

                            {{ $directorName }}

                            <span>Programme Director</span>

                        </div>

                    </div>

                </td>

                <td width="30%" align="right">

                    <div class="notice">

                        Any alteration or erasure

                        renders this result slip invalid

                    </div>

                </td>

            </tr>

        </table>

    </div>

</div>

</body>
</html>
