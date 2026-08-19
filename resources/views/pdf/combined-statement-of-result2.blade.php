<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Combined Statement of Result</title>
    <style>
        @font-face {
            font-family: 'Old English Text MT';
            src: url('{{ public_path("assets/fonts/oldenglishtextmt.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @page {
            size: A4 landscape;
            margin: 12mm 10mm 10mm 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Letterhead ──────────────────────────────────── */

        .letterhead {
            width: 100%;
            max-height: 120px;
            display: block;
        }

        /* ── Title block ─────────────────────────────────── */

        .title-block {
            text-align: center;
            margin: 8px 0 6px;
        }

        .title-office {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
            letter-spacing: 1px;
        }

        .title-main {
            font-family: 'Old English Text MT', serif;
            font-size: 22px;
            color: #000;
            text-decoration: underline;
            margin: 3px 0;
        }

        .title-session {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            font-weight: bold;
            color: #000;
            margin: 2px 0;
        }

        .title-exam {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            color: #000;
            margin: 2px 0;
        }

        .title-date {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #555;
            text-align: right;
            margin: 4px 0 0;
        }

        /* ── Data table ──────────────────────────────────── */

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            font-weight: bold;
            color: #000;
            margin-top: 6px;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tbody {
            display: table-row-group;
        }

        .data-table th {
            background: #f0f0f0;
            border: 1px solid #999;
            padding: 4px 3px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            vertical-align: middle;
            white-space: nowrap;
        }

        .data-table th.sn-col {
            width: 28px;
        }

        .data-table th.name-col {
            width: 140px;
            text-align: left;
        }

        .data-table th.fnd-col {
            width: 70px;
        }

        .data-table th.exam-col {
            width: 70px;
        }

        .data-table th.subject-col {
            width: auto;
        }

        .data-table th.grade-col {
            width: 38px;
        }

        .data-table th.point-col {
            width: 32px;
        }

        .data-table th.total-col {
            width: 40px;
        }

        .data-table th.remark-col {
            width: 50px;
        }

        .data-table td {
            border: 1px solid #999;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
        }

        .data-table td.sn-cell {
            text-align: center;
            font-size: 9px;
        }

        .data-table td.name-cell {
            text-align: left;
            font-size: 10px;
        }

        .data-table td.fnd-cell,
        .data-table td.exam-cell {
            font-size: 9px;
        }

        .data-table tr:nth-child(even) {
            background: #fafafa;
        }

        .data-table .row-pass {
            background: #f0fdf4;
        }

        .data-table .row-fail {
            background: #fef2f2;
        }

        .remark-pass {
            color: #16a34a;
            font-weight: bold;
        }

        .remark-fail {
            color: #dc2626;
            font-weight: bold;
        }

        /* ── Grading key ─────────────────────────────────── */

        .grading-key {
            margin-top: 8px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 9px;
            font-weight: bold;
            color: #000;
            line-height: 15px;
            white-space: pre-line;
        }

        /* ── Footer ──────────────────────────────────────── */

        .footer {
            margin-top: 8px;
            width: 100%;
            border-collapse: collapse;
        }

        .footer td {
            vertical-align: bottom;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }

        .footer-left {
            width: 45%;
        }

        .footer-right {
            width: 55%;
            text-align: right;
            font-style: italic;
            color: #555;
        }

        .stamp-container {
            position: relative;
            width: 120px;
            height: 80px;
        }

        .stamp-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 120px;
            height: 80px;
        }

        .signature-img {
            position: absolute;
            top: 20px;
            left: 5px;
            width: 105px;
            height: 35px;
        }

        .director-name {
            margin-top: 3px;
            font-size: 11px;
            font-weight: bold;
            line-height: 14px;
        }
    </style>
</head>
<body>

<table class="data-table">
    <thead>
    <tr>
        <th colspan="14" style="border: none; padding: 0; background: none;">
            <img
                src="{{ $settings->get('letterhead_image') ? Storage::url($settings->get('letterhead_image')) : asset('assets/jupeb/letterhead.png') }}"
                alt="JUPEB Letterhead"
                class="letterhead"
            >
            <div class="title-block">
                <div class="title-office">Office of the Director</div>
                <div class="title-main">Combined Statement of Result</div>
                <div class="title-session">{{ $academicSession }} Academic Session</div>
                <div class="title-exam">{{ $settings->get('result_year') ?: ($examYear ?: '2025') }} JUPEB EXAM (A-Level Equivalent)</div>
            </div>
            <div class="title-date">Date: {{ $issueDate }}</div>
        </th>
    </tr>
    <tr>
        <th class="sn-col">S/N</th>
        <th class="name-col">Student Name</th>
        <th class="fnd-col">Foundation No.</th>
        <th class="exam-col">Exam No.</th>
        <th class="subject-col">Subject 1</th>
        <th class="grade-col">Grade</th>
        <th class="point-col">Point</th>
        <th class="subject-col">Subject 2</th>
        <th class="grade-col">Grade</th>
        <th class="point-col">Point</th>
        <th class="subject-col">Subject 3</th>
        <th class="grade-col">Grade</th>
        <th class="point-col">Point</th>
        <th class="total-col">GP<br><span style="font-size:7px;font-weight:normal;">/16</span></th>
    </tr>
    </thead>
    <tbody>
    @forelse ($results as $index => $row)
        @php
            $student = $row['student'];
            $result  = $row['result'];
            $isPass  = $result->total_point >= 8;
        @endphp
        <tr @if($isPass) class="row-pass" @else class="row-fail" @endif>
            <td class="sn-cell">{{ $index + 1 }}</td>
            <td class="name-cell">{{ $student->lastNameFirst() }}</td>
            <td class="fnd-cell">{{ $student->foundation_number }}</td>
            <td class="exam-cell">{{ $student->examination_number ?? '—' }}</td>
            <td>{{ strtoupper($result->subject_one) }}</td>
            <td>{{ $result->grade_one->value }}</td>
            <td>{{ $result->point_one }}</td>
            <td>{{ strtoupper($result->subject_two) }}</td>
            <td>{{ $result->grade_two->value }}</td>
            <td>{{ $result->point_two }}</td>
            <td>{{ strtoupper($result->subject_three) }}</td>
            <td>{{ $result->grade_three->value }}</td>
            <td>{{ $result->point_three }}</td>
            <td style="font-weight:900;">{{ $result->total_point }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="14" style="text-align:center; padding:20px; color:#999;">No results to display.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="grading-key">Key to Grade: A = 70–100 (5 pts); B = 60–69 (4 pts); C = 50–59 (3 pts); D = 45–49 (2 pts); E = 40–44 (1 pt); F = 0–39 (0 pts). X = Absent; Q = Cancelled; W = Withheld. One point added if all 3 subjects passed.</div>

<table class="footer">
    <tr>
        <td class="footer-left">
            <div class="stamp-container">
                <img src="{{ $stamp }}" class="stamp-img">
                <img src="{{ $signature }}" class="signature-img">
            </div>
            <div class="director-name">
                {{ $directorName }}<br>
                <span style="font-weight:normal;font-size:9px;">Programme Director</span>
            </div>
        </td>
        <td class="footer-right">
            Any alteration or erasure renders this result slip invalid.
        </td>
    </tr>
</table>

</body>
</html>
