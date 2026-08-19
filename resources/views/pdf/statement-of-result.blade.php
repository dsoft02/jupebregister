<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Statement of Result</title>
    <style>
        @font-face {
            font-family: 'Old English Text MT';
            src: url('{{ asset("assets/fonts/oldenglishtextmt.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @page {
            size: A4;
            margin: 0;
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

        .page {
            width: 100%;
            position: relative;
            background: #fff;
        }

        .letterhead {
            display: block;
            width: 100%;
            height: auto;
            max-height: 178px;
        }

        /* ── Content card ─────────────────────────────────── */

        .content-card {
            position: relative;
            margin: 1px 30px 0;
            border: 1.5px solid #CCC;
            border-radius: 20px;
            overflow: hidden;
            padding-bottom: 5px;
        }

        .watermark {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 149px;
            width: 466px;
            opacity: 0.08;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        /* ── Header row ───────────────────────────────────── */

        .header-container {
            position: relative;
            margin-top: 55px;
            min-height: 100px;
        }

        .header {
            text-align: center;
        }

        .header-title {
            font-family: 'Old English Text MT', serif;
            font-size: 36px;
            color: #000;
            text-decoration: underline;
        }

        .header-right {
            position: absolute;
            top: -40px;
            right: 10px;
            text-align: right;
        }

        .header-date {
            display: block;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            color: #000;
            text-align: right;
            margin-bottom: 4px;
        }

        .passport-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 0.5px solid #ccc;
            display: block;
            margin-left: auto;
            margin-right: 0;
        }

        .passport-placeholder {
            width: 120px;
            height: 120px;
            border: 0.5px solid #ccc;
            text-align: center;
            line-height: 55px;
            font-size: 9px;
            color: #94a3b8;
            font-family: Arial, sans-serif;
            margin-left: auto;
            margin-right: 0;
        }

        /* ── Name / info block ─────────────────────────────── */
        .student-info-table {
            width: 610px;
            margin: 5px 0 0 40px;
            border-collapse: collapse;
            font-family: "Times New Roman", Times, serif;
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }

        .student-info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .student-info-table .label {
            width: 200px;
            white-space: nowrap;
        }

        .student-info-table .value {
            width: 360px;
        }

        /* ── Exam heading ──────────────────────────────────── */

        .exam-heading {
            margin-top: 20px;
            margin-left: 47px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            text-decoration: underline;
        }

        /* ── Subject section ─────────────────────────────── */

        .subject-table {
            width: 648px;
            margin: 18px 0 0 47px;
            border-collapse: separate;
            border-spacing: 0 3px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: bold;
            color: #000;
        }

        .subject-table th {
            font-size: 18px;
            text-decoration: underline;
            text-align: left;
            padding: 0 0 8px 0;
        }

        .subject-table th.grade-col {
            text-align: center;
            width: 205px;
        }

        .subject-table th.point-col {
            text-align: center;
            width: 170px;
        }

        .subject-table td {
            padding: 0;
            vertical-align: middle;
        }

        .subject-table td.subject-cell {
            width: 273px;
            padding-right: 8px;
        }

        .subject-table td.grade-cell {
            width: 205px;
            padding-right: 8px;
            text-align: center;
        }

        .subject-table td.point-cell {
            width: 170px;
            text-align: center;
        }

        .subject-table .subject-box {
            width: 205px;
            height: 30px;
            line-height: 26px;
            border: 1px solid #CCC;
            border-radius: 8px;
            background: #fff;
            padding: 0 0 0 20px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            font-weight: bold;
        }

        .subject-table .grade-box,
        .subject-table .point-box {
            width: 100px;
            height: 30px;
            line-height: 26px;
            text-align: center;
            border: 1px solid #CCC;
            background: #fff;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            font-weight: bold;
            padding: 0;
            display: inline-block;
        }

        .subject-table .total-row td {
            padding-top: 4px;
        }

        .subject-table .total-row .grade-box,
        .subject-table .total-row .point-box {
            width: 100px;
            display: inline-block;
        }

        /* ── Grade point summary ───────────────────────────── */

        .grade-point {
            margin-top: 7px;
            margin-left: 297px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        /* ── Key to grade ──────────────────────────────────── */

        .key-to-grade {
            margin-top: 0;
            margin-left: 63px;
            margin-right: 63px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            font-weight: bold;
            line-height: 28px;
            color: #000;
            white-space: pre-line;
        }

        /* ── Footer ────────────────────────────────────────── */
        .footer{
            margin:10px 30px 15px;
        }

        .footer-table{
            width:100%;
            border-collapse:collapse;
        }

        .footer-table td{
            vertical-align:bottom;
        }

        .footer-left{
            width:45%;
        }

        .footer-right{
            width:55%;
            text-align:right;
            font-family:Arial,sans-serif;
            font-size:14px;
        }

        .stamp-container{
            position:relative;
            width:170px;
            height:110px;
        }

        .stamp-img{
            position:absolute;
            top:0;
            left:0;
            width:170px;
            height:110px;
        }

        .signature-img{
            position:absolute;
            top:30px;
            left:10px;
            width:145px;
            height:50px;
        }

        .director-name{
            margin-top:6px;
            font-size:18px;
            font-weight:bold;
            line-height:20px;
        }
    </style>
</head>
<body>
<div class="page">
    {{-- Letterhead --}}
    <img
        src="{{ $settings->get('letterhead_image') ? Storage::url($settings->get('letterhead_image')) : asset('assets/jupeb/letterhead.png') }}"
        alt="JUPEB Letterhead"
        class="letterhead"
    >

    {{-- Content card --}}
    <div class="content-card">
        {{-- Watermark --}}
        <img
            src="{{ $settings->get('watermark_image') ? Storage::url($settings->get('watermark_image')) : asset('assets/jupeb/watermark.png') }}"
            alt=""
            aria-hidden="true"
            class="watermark"
        >

        <div class="content">
            {{-- Header: date, title, passport --}}
            <div class="header-container">
            <div class="header-right">
                <span class="header-date">
                    Date: {{ \Carbon\Carbon::createFromFormat('d/m/Y', $issueDate)->format('jS F, Y') }}
                </span>
                @if ($passport)
                    <img src="{{ $passport }}" alt="Passport" class="passport-photo">
                @else
                    <div class="passport-placeholder">No Photo</div>
                @endif
            </div>
            <div class="header">
                <span class="header-title">Statement of Result</span>
            </div>
            </div>

            <table class="student-info-table">
                <tr>
                    <td class="label">Name (Surname First):</td>
                    <td class="value">{{ $student->lastNameFirst() }}</td>
                </tr>
                <tr>
                    <td class="label">Examination Year:</td>
                    <td class="value">{{ $result->session }} Academic Session</td>
                </tr>
                <tr>
                    <td class="label">Foundation Number:</td>
                    <td class="value">{{ $student->foundation_number }}</td>
                </tr>
                <tr>
                    <td class="label">Examination Number :</td>
                    <td class="value">{{ $student->examination_number ?? '—' }}</td>
                </tr>
            </table>

            {{-- Exam heading --}}
            <div class="exam-heading">{{ $resultYear }} JUPEB EXAM (A-Level Equivalent)</div>

            {{-- Subject section --}}
            <table class="subject-table">
                <thead>
                <tr>
                    <th class="subject-col">SUBJECT</th>
                    <th class="grade-col">GRADE LETTER</th>
                    <th class="point-col">GRADE POINT</th>
                </tr>
                </thead>
                <tbody>
                @foreach($result->subjects() as $subject)
                    <tr>
                        <td class="subject-cell">
                            <div class="subject-box">{{ strtoupper($subject['subject']) }}</div>
                        </td>
                        <td class="grade-cell">
                            <div class="grade-box">{{ $subject['grade']->value }}</div>
                        </td>
                        <td class="point-cell">
                            <div class="point-box">{{ $subject['point'] }}</div>
                        </td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td class="grade-cell">
                        <div class="grade-box">TOTAL</div>
                    </td>
                    <td class="point-cell">
                        <div class="point-box">{{ $result->total_point }}</div>
                    </td>
                </tr>
                </tbody>
            </table>

            {{-- Grade point --}}
            <div class="grade-point">Grade Point = {{ $result->gradePointLabel() }}</div>

            {{-- Key to grade --}}
            <div class="key-to-grade">
                Key to Grade:
                A = 70-100 (5 Points); B=60-69 (4 Points); C=50 - 59 (3 Points); D=45 - 49 (2Points)
                E= 40 - 45 (1 Point); F(Fail)=0 - 39 (0 Point)
                X = Absent; Q = Cancelled; W = Withheld
                One Point added if all 3 subjects passed.
            </div>

            {{-- Footer: stamp/signature + director + alteration note --}}
            <div class="footer">
                <table class="footer-table">
                    <tr>
                        <td class="footer-left">
                            <div class="stamp-container">
                                <img src="{{ $settings->get('official_stamp') ? Storage::url($settings->get('official_stamp')) : asset('assets/jupeb/stamp.png') }}"
                                     class="stamp-img">

                                <img src="{{ $settings->get('director_signature') ? Storage::url($settings->get('director_signature')) : asset('assets/jupeb/signature.png') }}"
                                     class="signature-img">
                            </div>

                            <div class="director-name">
                                {{ $settings->get('director_name', 'Director') }}<br>
                                Programme Director
                            </div>
                        </td>

                        <td class="footer-right">
                            Any alteration or erasure renders this result slip invalid
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
