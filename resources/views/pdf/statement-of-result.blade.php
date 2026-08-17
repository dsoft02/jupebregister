<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Statement of Result</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap");

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
            width: 794px;
            height: 1123px;
            position: relative;
            overflow: hidden;
            background: #fff;
        }

        .letterhead {
            display: block;
            width: 794px;
            height: 179px;
        }

        /* ── Content card ─────────────────────────────────── */

        .content-card {
            position: relative;
            margin: 1px 32px 0;
            border: 1.5px solid #CCC;
            border-radius: 20px;
            overflow: hidden;
            padding-bottom: 24px;
        }

        .watermark {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 149px;
            width: 466px;
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        /* ── Header row ───────────────────────────────────── */

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-top: 8px;
            padding: 0 14px 0 20px;
        }

        .header-left {
            width: 183px;
            flex-shrink: 0;
        }

        .header-center {
            flex: 1;
            text-align: center;
            padding-top: 59px;
        }

        .header-title {
            font-family: 'UnifrakturMaguntia', cursive;
            font-size: 36px;
            color: #000;
            text-decoration: underline;
        }

        .header-right {
            width: 183px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1px;
        }

        .header-date {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            color: #000;
            text-align: right;
        }

        .passport-photo {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border: 0.5px solid #ccc;
        }

        .passport-placeholder {
            width: 90px;
            height: 90px;
            border: 0.5px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #94a3b8;
            font-family: Arial, sans-serif;
        }

        /* ── Name / info block ─────────────────────────────── */

        .name-block {
            margin: 15px 20px 0 47px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: 700;
            font-size: 20px;
            line-height: 30px;
            color: #000;
            white-space: pre;
        }

        /* ── Exam heading ──────────────────────────────────── */

        .exam-heading {
            margin-top: 44px;
            margin-left: 47px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            text-decoration: underline;
        }

        /* ── Table headers ─────────────────────────────────── */

        .table-headers {
            display: flex;
            margin-top: 41px;
            margin-left: 47px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .table-headers span {
            text-decoration: underline;
        }

        /* ── Subject rows ──────────────────────────────────── */

        .subject-row {
            display: flex;
            align-items: center;
            margin-left: 47px;
        }

        .subject-box {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #CCC;
            background: #fff;
            font-weight: bold;
            color: #000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.25);
            font-family: 'Times New Roman', Times, serif;
            height: 35px;
            font-size: 16px;
            flex-shrink: 0;
        }

        .subject-box.rounded {
            border-radius: 8px;
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
            margin-top: 12px;
            margin-left: 63px;
            margin-right: 63px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            font-weight: bold;
            line-height: 28px;
            color: #000;
            white-space: pre-line;
        }

        /* ── Footer ────────────────────────────────────────── */

        .footer {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-top: 3px;
            margin-left: 33px;
            margin-right: 20px;
        }

        .stamp-container {
            position: relative;
            width: 168px;
            height: 115px;
        }

        .stamp-img {
            position: absolute;
            left: 0;
            top: 0;
            width: 168px;
            height: 115px;
        }

        .signature-img {
            position: absolute;
            left: 9px;
            top: 31px;
            width: 150px;
            height: 53px;
        }

        .director-name {
            font-family: 'Times New Roman', Times, serif;
            font-size: 20px;
            font-weight: bold;
            line-height: 18px;
            color: #000;
            white-space: pre-line;
        }

        .alteration-note {
            margin-top: 167px;
            width: 346px;
            text-align: right;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Letterhead --}}
        <img
            src="{{ $settings->get('letterhead') ? Storage::url($settings->get('letterhead')) : asset('assets/jupeb/letterhead.png') }}"
            alt="JUPEB Letterhead"
            class="letterhead"
        >

        {{-- Content card --}}
        <div class="content-card">
            {{-- Watermark --}}
            <img
                src="{{ $settings->get('watermark') ? Storage::url($settings->get('watermark')) : asset('assets/jupeb/watermark.png') }}"
                alt=""
                aria-hidden="true"
                class="watermark"
            >

            <div class="content">
                {{-- Header: date, title, passport --}}
                <div class="header">
                    <div class="header-left"></div>
                    <div class="header-center">
                        <span class="header-title">Statement of Result</span>
                    </div>
                    <div class="header-right">
                        <span class="header-date">Date: {{ $issueDate }}</span>
                        @if ($passport)
                            <img src="{{ $passport }}" alt="Passport" class="passport-photo">
                        @else
                            <div class="passport-placeholder">No Photo</div>
                        @endif
                    </div>
                </div>

                {{-- Name / exam info --}}
                <div class="name-block">Name (Surname First):	{{ $student->lastNameFirst() }}
Examination Year:	{{ $student->session }} Academic Session
Foundation Number:	{{ $student->foundation_number }}
Examination Number :	{{ $student->examination_number ?? '—' }}</div>

                {{-- Exam heading --}}
                <div class="exam-heading">2025 JUPEB EXAM (A-Level Equivalent)</div>

                {{-- Table headers --}}
                <div class="table-headers">
                    <span style="width: 205px; margin-left: 0;">SUBJECT</span>
                    <span style="width: 100px; margin-left: 68px;">GRADE LETTER</span>
                    <span style="width: 100px; margin-left: 105px;">GRADE POINT</span>
                </div>

                {{-- Subject rows --}}
                @foreach ($result->subjects() as $subject)
                    <div class="subject-row" style="margin-top: {{ $loop->first ? '9px' : '5px' }};">
                        <div class="subject-box rounded" style="width: 205px;">{{ strtoupper($subject['subject']) }}</div>
                        <div class="subject-box" style="width: 100px; margin-left: 68px;">{{ $subject['grade']->value }}</div>
                        <div class="subject-box" style="width: 100px; margin-left: 105px;">{{ $subject['point'] }}</div>
                    </div>
                @endforeach

                {{-- Total row --}}
                <div class="subject-row" style="margin-top: 5px; margin-left: 273px;">
                    <div class="subject-box" style="width: 100px;">TOTAL</div>
                    <div class="subject-box" style="width: 100px; margin-left: 105px;">{{ $result->total_point }}</div>
                </div>

                {{-- Grade point --}}
                <div class="grade-point">Grade Point = {{ $result->gradePointLabel() }}</div>

                {{-- Key to grade --}}
                <div class="key-to-grade">Key to Grade:
A = 70-100 (5 Points); B=60-69 (4 Points); C=50 - 59 (3 Points); D=45 - 49 (2Points)
E= 40 - 45 (1 Point); F(Fail)=0 - 39 (0 Point)
X = Absent; Q = Cancelled; W = Withheld
One Point added if all 3 subjects passed.</div>

                {{-- Footer: stamp/signature + director + alteration note --}}
                <div class="footer">
                    <div>
                        <div class="stamp-container">
                            <img
                                src="{{ $settings->get('official_stamp') ? Storage::url($settings->get('official_stamp')) : asset('assets/jupeb/stamp.png') }}"
                                alt="JUPEB Stamp"
                                class="stamp-img"
                            >
                            <img
                                src="{{ $settings->get('director_signature') ? Storage::url($settings->get('director_signature')) : asset('assets/jupeb/signature.png') }}"
                                alt="Signature"
                                class="signature-img"
                            >
                        </div>
                        <div class="director-name">{{ $settings->get('director_name', 'Director') }}
Programme Director</div>
                    </div>
                    <div class="alteration-note">
                        Any alteration or erasure renders this result slip invalid
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
