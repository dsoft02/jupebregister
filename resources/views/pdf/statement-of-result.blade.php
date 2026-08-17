<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Statement of Result</title>
    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1F2937;
        }

        /*
         * The official letterhead (header, logos, Office of the Director
         * design and watermark) is a full-page background image. Only the
         * dynamic fields below are rendered on top of it.
         */
        .letterhead {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }

        .letterhead img {
            width: 210mm;
            height: 297mm;
        }

        .content {
            position: relative;
            z-index: 1;
            padding: 40mm 16mm 12mm 18mm;
            width: 210mm;
        }

        .row { width: 100%; }
        .clearfix::after { content: ""; display: block; clear: both; }

        .field { font-size: 9.5pt; line-height: 1.45; margin-bottom: 1.4mm; }

        .field-label { font-weight: bold; }

        .date-block {
            float: right;
            text-align: right;
            font-size: 9.5pt;
            margin-bottom: 5mm;
        }

        .student-name {
            text-align: center;
            font-size: 12.5pt;
            font-weight: bold;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            margin: 1mm 0 4mm 0;
        }

        .passport-frame {
            float: right;
            width: 22mm;
            height: 26mm;
            border: 0.4mm solid #1F2937;
            margin: 0 0 4mm 4mm;
            text-align: center;
        }

        .passport-frame img {
            width: 22mm;
            height: 26mm;
        }

        .passport-frame .empty {
            line-height: 26mm;
            font-size: 8pt;
            color: #94a3b8;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5mm 0 3mm 0;
        }

        .result-table th,
        .result-table td {
            border: 0.3mm solid #1F2937;
            padding: 2.6mm 3mm;
            font-size: 9.5pt;
        }

        .result-table th {
            background-color: #d9f2e3;
            font-weight: bold;
            text-align: left;
        }

        .result-table td.grade,
        .result-table td.point,
        .result-table td.total-value {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background-color: #F8FAFC;
        }

        .grade-point {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 2mm;
        }

        .signature-area {
            margin-top: 12mm;
            width: 100%;
        }

        .signature-box {
            float: right;
            width: 70mm;
            text-align: center;
            font-size: 9.5pt;
        }

        .signature-box .signature-img {
            height: 16mm;
            margin-bottom: 1mm;
        }

        .signature-box .signature-img img {
            max-height: 16mm;
            max-width: 50mm;
        }

        .signature-box .stamp-img img {
            max-height: 16mm;
            max-width: 22mm;
        }

        .signature-box .signature-name {
            font-weight: bold;
            border-top: 0.3mm solid #1F2937;
            padding-top: 1.2mm;
        }

        .vice-chancellor {
            font-size: 8pt;
            color: #475569;
            margin-top: 10mm;
            text-align: center;
        }

        .footer {
            position: absolute;
            bottom: 8mm;
            left: 0;
            width: 210mm;
            text-align: center;
            font-size: 8.5pt;
            font-style: italic;
            color: #1F2937;
        }
    </style>
</head>
<body>
    @if ($letterhead)
        <div class="letterhead">
            <img src="{{ $letterhead }}" alt="PAAU Foundation School">
        </div>
    @endif

    <div class="content">
        <div class="date-block clearfix">
            <div>Date: <strong>{{ $issueDate }}</strong></div>
        </div>

        <div class="row clearfix">
            <div class="passport-frame">
                @if ($passport)
                    <img src="{{ $passport }}" alt="Passport">
                @else
                    <span class="empty">No Photo</span>
                @endif
            </div>

            <div class="field">
                <span class="field-label">Student Name:</span>
                <strong>{{ $student->lastNameFirst() }}</strong>
            </div>
            <div class="field">
                <span class="field-label">Academic Session:</span> {{ $student->session }}
            </div>
            <div class="field">
                <span class="field-label">Foundation Number:</span> {{ $student->foundation_number }}
            </div>
            <div class="field">
                <span class="field-label">Examination Number:</span> {{ $student->examination_number ?? '—' }}
            </div>
            <div class="field">
                <span class="field-label">Subjects:</span>
                {{ implode(' / ', $student->chosenSubjectNames()) }}
            </div>
        </div>

        <table class="result-table">
            <thead>
                <tr>
                    <th style="width: 55%;">Subject</th>
                    <th style="width: 22%;">Grade</th>
                    <th style="width: 23%;">Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result->subjects() as $subject)
                    <tr>
                        <td>{{ $subject['subject'] }}</td>
                        <td class="grade">{{ $subject['grade']->value }}</td>
                        <td class="point">{{ $subject['point'] }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Bonus Point</td>
                    <td></td>
                    <td class="total-value">{{ $result->bonus_point }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Points</td>
                    <td></td>
                    <td class="total-value">{{ $result->total_point }}</td>
                </tr>
            </tbody>
        </table>

        <div class="grade-point">
            Grade Point: {{ $result->gradePointLabel() }}
        </div>

        <div class="signature-area clearfix">
            <div class="signature-box">
                @if ($stamp)
                    <div class="stamp-img"><img src="{{ $stamp }}" alt="Official Stamp"></div>
                @endif
                @if ($signature)
                    <div class="signature-img"><img src="{{ $signature }}" alt="Director Signature"></div>
                @endif
                <div class="signature-name">{{ $settings->get('director_name', 'Director') }}</div>
                <div>{{ $settings->get('director_credentials') }}</div>
            </div>
        </div>

        <div class="vice-chancellor">
            {{ $settings->get('vice_chancellor_name') }}
            @if ($settings->get('vice_chancellor_credentials'))
                <br>{{ $settings->get('vice_chancellor_credentials') }}
            @endif
        </div>
    </div>

    <div class="footer">
        Any alteration or erasure renders this result slip invalid.
    </div>
</body>
</html>
