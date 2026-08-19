<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Combined Statement of Result</title>

    <style>
        @font-face {
            font-family: "Old English Text MT";
            src: url("{{ public_path('assets/fonts/oldenglishtextmt.ttf') }}") format("truetype");
        }

        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", serif;
            color: #000;
            background: #fff;
            font-size: 12px;
        }

        .certificate {
            position: relative;
            padding: 10px;
            min-height: 186mm;
        }

        .watermark {
            position: absolute;
            width: 390px;
            left: 50%;
            top: 52%;
            margin-left: -195px;
            margin-top: -195px;
            opacity: .05;
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
        }

        .letterhead-wrap {
            width: 100%;
            text-align: center;
            margin-bottom: 4px;
        }

        .letterhead {
            display: inline-block;
            height: 150px;
            width: auto;
            max-width: 100%;
        }

        .title {
            position: relative;
            text-align: center;
            margin-bottom: 10px;
        }

        .issue-date {
            position: absolute;
            right: 5px;
            top: 2px;
            font-size: 16px;
            font-weight: bold;
        }

        .main-title {
            font-size: 24px;
            font-weight: 900;
            text-decoration: underline;
        }

        .session {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
        }

        .exam {
            font-size: 17px;
            font-weight: bold;
            margin-top: 3px;
        }

        table.result-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 10px;
        }

        .result-table th,
        .result-table td {
            border: 1px solid #333;
            padding: 8px 6px;
            vertical-align: middle;
        }

        .result-table thead th {
            background: #E8EEF8;
            text-align: center;
            font-weight: bold;
        }

        thead {
            display: table-header-group;
        }
        .page-gap th{
            border:none !important;
            padding:0;
            height:12px; /* adjust 10–18px to taste */
            background:transparent !important;
        }

        .group {
            background: #D9E3F3;
            font-size: 11px;
        }

        .name {
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .subject {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            line-height: 1.15;
        }

        .grade,
        .point {
            width: 30px;
            min-width: 10px;
            max-width: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            white-space: nowrap;
            padding: 8px 2px;
        }

        .total {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .remark {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* ── Bottom three-column section ───────────────────── */

        .bottom-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .bottom-section td {
            vertical-align: bottom;
        }

        .key-cell {
            width: 40%;
            padding-right: 15px;
        }

        .stamp-cell {
            width: 35%;
            text-align: center;
        }

        .notice-cell {
            width: 25%;
            text-align: right;
        }

        .key-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .key-text {
            font-size: 14px;
            font-weight: bold;
            line-height: 2;
        }

        .stamp-wrap {
            position: relative;
            width: 180px;
            height: 148px;
            margin: 0 auto;
        }

        .stamp {
            position: absolute;
            left: 10px;
            top: 18px;
            width: 140px;
            z-index: 1;
        }

        .signature {
            position: absolute;
            left: 38px;
            top: 45px;
            width: 105px;
            z-index: 2;
        }

        .director {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            line-height: 18px;
        }

        .director span {
            display: block;
            font-size: 14px;
            font-weight: bold;
        }

        .notice {
            border: 1px solid #777;
            border-radius: 8px;
            padding: 12px;
            width: 90%;
            margin-left: auto;
            text-align: center;
            font-size: 12px;
            line-height: 1.45;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>

</head>
<body>

<div class="certificate">

    <img src="{{ asset('assets/jupeb/watermark.png') }}" class="watermark">

    <div class="content">
        <div class="letterhead-wrap">
            <img src="{{ $settings->get('letterhead_image') ? asset('storage/' . $settings->get('letterhead_image')) : asset('assets/jupeb/watermark.png') }}"
                 alt=""
                 aria-hidden="true"
                 class="watermark"
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
                {{ $academicSession }} ACADEMIC SESSION
            </div>

            <div class="exam">
                {{ $resultYear }} JUPEB EXAMINATION (A-LEVEL EQUIVALENT)
            </div>

        </div>
        <table class="result-table">
            <colgroup>
                <col style="width:32px;">   <!-- S/N -->

                <col style="width:135px;">  <!-- Name -->
                <col style="width:95px;">   <!-- Foundation -->
                <col style="width:102px;">  <!-- Exam -->

                <col style="width:86px;">   <!-- Subject 1 -->
                <col style="width:26px;">   <!-- Grade -->
                <col style="width:26px;">   <!-- Point -->

                <col style="width:86px;">   <!-- Subject 2 -->
                <col style="width:26px;">   <!-- Grade -->
                <col style="width:26px;">   <!-- Point -->

                <col style="width:86px;">   <!-- Subject 3 -->
                <col style="width:26px;">   <!-- Grade -->
                <col style="width:26px;">   <!-- Point -->

                <col style="width:78px;">   <!-- Grade Point -->
                <col style="width:68px;">   <!-- Remarks -->
            </colgroup>

            <thead>
            <tr class="page-gap">
                <th colspan="15"></th>
            </tr>
            <tr>
                <th rowspan="2" class="group" style="width:3%;">S/N</th>
                <th colspan="3" class="group" style="width:40%;">STUDENT INFORMATION</th>
                <th colspan="9" class="group" style="width:45%;">SUBJECTS</th>
                <th colspan="2" class="group" style="width:12%;">TOTAL</th>
            </tr>

            <tr>
                <th>NAME (SURNAME FIRST)</th>
                <th class="nowrap">FOUNDATION NO.</th>
                <th class="nowrap">EXAMINATION NO.</th>
                <th>SUBJECT 1</th>
                <th>GRADE</th>
                <th>POINT</th>
                <th>SUBJECT 2</th>
                <th>GRADE</th>
                <th>POINT</th>
                <th>SUBJECT 3</th>
                <th>GRADE</th>
                <th>POINT</th>
                <th>GRADE POINT<br>(OUT OF 16)</th>
                <th>REMARKS</th>
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

                    <td style="text-align:center;">{{ $index+1 }}</td>

                    <td class="name">
                        {{ $student->lastNameFirst() }}
                    </td>

                    <td class="nowrap">{{ $student->foundation_number }}</td>

                    <td class="nowrap">{{ $student->examination_number ?? '—' }}</td>

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

        <table class="bottom-section">

            <tr>

                <td class="key-cell">

                    <div class="key-title">Key to Grade:</div>

                    <div class="key-text">
                        A = 70–100 (5 Points); B = 60–69 (4 Points); C = 50–59 (3 Points)<br>
                        D = 45–49 (2 Points); E = 40–45 (1 Point); F(Fail) = 0–39 (0 Point)<br>
                        X = Absent; Q = Cancelled; W = Withheld<br>
                        One Point added if all 3 subjects passed.
                    </div>

                </td>

                <td class="stamp-cell">

                    <div class="stamp-wrap">

                        @if($stamp)
                        <img src="{{ $stamp }}" class="stamp">
                        @endif

                        @if($signature)
                        <img src="{{ $signature }}" class="signature">
                        @endif

                        <div class="director">
                            {{ $directorName }}
                            <span>Programme Director</span>
                        </div>

                    </div>

                </td>

                <td class="notice-cell">

                    <div class="notice">
                        Any alteration or erasure renders this result slip invalid
                    </div>

                </td>

            </tr>

        </table>

    </div>

</div>

</body>
</html>
