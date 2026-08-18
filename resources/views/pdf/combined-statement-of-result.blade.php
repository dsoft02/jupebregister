<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Combined Statement of Result</title>

    <style>
        @font-face{
            font-family:"Old English Text MT";
            src:url('{{ asset("assets/fonts/oldenglishtextmt.ttf") }}') format("truetype");
        }

        @page{
            size:A4;
            margin:190px 28px 125px;
        }

        *{margin:0;padding:0;box-sizing:border-box;}

        body{
            font-family:"Times New Roman",serif;
            color:#000;
            -webkit-print-color-adjust:exact;
            print-color-adjust:exact;
        }

        /* ===========================
           Fixed Header
        =========================== */

        .header{
            position:fixed;
            top:-190px;
            left:-28px;
            right:-28px;
            height:180px;
        }

        .letterhead{
            width:100%;
            height:auto;
            max-height:178px;
        }

        /* ===========================
           Fixed Footer
        =========================== */

        .footer{
            position:fixed;
            bottom:-125px;
            left:-28px;
            right:-28px;
            height:115px;
            padding:0 30px;
        }

        .footer-table{
            width:100%;
            border-collapse:collapse;
        }

        .footer-table td{
            vertical-align:bottom;
        }

        .footer-left{width:50%;}
        .footer-right{
            width:50%;
            text-align:right;
            font-family:Arial;
            font-size:13px;
        }

        .stamp-container{
            position:relative;
            width:170px;
            height:90px;
        }

        .stamp{
            position:absolute;
            width:170px;
            height:90px;
        }

        .signature{
            position:absolute;
            width:145px;
            top:23px;
            left:10px;
        }

        .director{
            margin-top:5px;
            font-size:18px;
            font-weight:bold;
            line-height:20px;
        }

        /* ===========================
           Watermark
        =========================== */

        .watermark{
            position:fixed;
            top:280px;
            left:50%;
            width:480px;
            margin-left:-240px;
            opacity:.08;
            z-index:-1;
        }

        /* ===========================
           Main Content
        =========================== */

        .title{
            text-align:center;
            margin-bottom:15px;
        }

        .main-title{
            font-size:28px;
            font-weight:bold;
            text-decoration:underline;
        }

        .session{
            font-size:18px;
            font-weight:bold;
            margin-top:4px;
        }

        .exam{
            margin-top:6px;
            font-size:16px;
            font-weight:bold;
        }

        .date{
            text-align:right;
            font-size:14px;
            font-weight:bold;
            margin-bottom:10px;
        }

        /* ===========================
           Table
        =========================== */

        .result-table{
            width:100%;
            border-collapse:collapse;
            font-size:11px;
        }

        .result-table thead{
            display:table-header-group;
        }

        .result-table tr{
            page-break-inside:avoid;
        }

        .result-table th,
        .result-table td{
            border:1px solid #444;
            padding:6px 5px;
            vertical-align:middle;
        }

        .result-table th{
            background:#eef3fb;
            text-align:center;
            font-weight:bold;
        }

        .group{
            background:#dde7f8;
            font-size:12px;
        }

        .sn{
            width:35px;
            text-align:center;
        }

        .name{
            width:190px;
        }

        .foundation{width:115px;}
        .examno{width:115px;}
        .subject{width:125px;}
        .grade{width:40px;text-align:center;}
        .point{width:45px;text-align:center;}
        .total{width:85px;text-align:center;font-weight:bold;}
        .remark{width:75px;text-align:center;font-weight:bold;}

        .key{
            margin-top:18px;
            font-size:14px;
            line-height:24px;
            font-weight:bold;
        }
    </style>
</head>

<body>

{{-- Fixed Header --}}
<div class="header">
    <img
        src="{{ $settings->get('letterhead_image') ? Storage::url($settings->get('letterhead_image')) : asset('assets/jupeb/letterhead.png') }}"
        class="letterhead">
</div>

{{-- Fixed Watermark --}}
<img
    src="{{ $settings->get('watermark_image') ? Storage::url($settings->get('watermark_image')) : asset('assets/jupeb/watermark.png') }}"
    class="watermark">

{{-- Fixed Footer --}}
<div class="footer">
    <table class="footer-table">
        <tr>

            <td class="footer-left">

                <div class="stamp-container">

                    <img
                        src="{{ $settings->get('official_stamp') ? Storage::url($settings->get('official_stamp')) : asset('assets/jupeb/stamp.png') }}"
                        class="stamp">

                    <img
                        src="{{ $settings->get('director_signature') ? Storage::url($settings->get('director_signature')) : asset('assets/jupeb/signature.png') }}"
                        class="signature">

                </div>

                <div class="director">
                    {{ $settings->get('director_name','Director') }}<br>
                    Programme Director
                </div>

            </td>

            <td class="footer-right">
                Any alteration or erasure renders this result slip invalid
            </td>

        </tr>
    </table>
</div>

{{-- Main Content --}}

<div class="date">
    Date:
    {{ \Carbon\Carbon::createFromFormat('d/m/Y',$issueDate)->format('jS F, Y') }}
</div>

<div class="title">
    <div class="main-title">COMBINED STATEMENT OF RESULT</div>

    <div class="session">
        {{ $students->first()->result->session ?? '2025/2026' }} Academic Session
    </div>

    <div class="exam">
        2025 JUPEB EXAMINATION (A-LEVEL EQUIVALENT)
    </div>
</div>

<table class="result-table">

    <thead>

    <tr class="group">
        <th rowspan="2" class="sn">S/N</th>

        <th colspan="3">STUDENT INFORMATION</th>

        <th colspan="9">SUBJECTS</th>

        <th colspan="2">TOTAL</th>
    </tr>

    <tr>

        <th class="name">NAME (SURNAME FIRST)</th>
        <th class="foundation">FOUNDATION NO.</th>
        <th class="examno">EXAMINATION NO.</th>

        <th class="subject">SUBJECT 1</th>
        <th class="grade">GRADE</th>
        <th class="point">POINT</th>

        <th class="subject">SUBJECT 2</th>
        <th class="grade">GRADE</th>
        <th class="point">POINT</th>

        <th class="subject">SUBJECT 3</th>
        <th class="grade">GRADE</th>
        <th class="point">POINT</th>

        <th class="total">GRADE POINT (OUT OF 16)</th>
        <th class="remark">REMARKS</th>

    </tr>

    </thead>

    <tbody>

    @foreach($students as $index => $student)

        @php
            $result=$student->result;
            $subjects=$result->subjects();
        @endphp

        <tr>

            <td class="sn">{{ $index+1 }}</td>

            <td>{{ $student->lastNameFirst() }}</td>
            <td>{{ $student->foundation_number }}</td>
            <td>{{ $student->examination_number }}</td>

            @for($i=0;$i<3;$i++)

                <td>{{ strtoupper($subjects[$i]['subject'] ?? '-') }}</td>

                <td class="grade">
                    {{ $subjects[$i]['grade']->value ?? '-' }}
                </td>

                <td class="point">
                    {{ $subjects[$i]['point'] ?? '-' }}
                </td>

            @endfor

            <td class="total">{{ $result->gradePointLabel() }}</td>

            <td class="remark">
                {{ $result->total_point >= 8 ? 'PASS':'FAIL' }}
            </td>

        </tr>

    @endforeach

    </tbody>

</table>

<div class="key">
    Key to Grade:<br>

    A = 70–100 (5 Points); B = 60–69 (4 Points); C = 50–59 (3 Points); D = 45–49 (2 Points)<br>

    E = 40–45 (1 Point); F(Fail) = 0–39 (0 Point)<br>

    X = Absent; Q = Cancelled; W = Withheld<br>

    One point added if all three subjects are passed.
</div>

</body>
</html>
