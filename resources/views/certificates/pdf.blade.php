
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificate->certificate_type }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #25313C;
            font-family: DejaVu Serif, serif;
        }

        .certificate {
            position: relative;
            width: 297mm;
            height: 210mm;
            padding: 13mm;
            background: #FFFFFF;
        }

        .outer-border {
            position: relative;
            height: 100%;
            border: 4px solid #123B5D;
            padding: 5mm;
        }

        .inner-border {
            position: relative;
            height: 100%;
            border: 1.5px solid #B89B5E;
            padding: 12mm 16mm;
            text-align: center;
        }

        .school-name {
            font-family: DejaVu Sans, sans-serif;
            font-size: 25px;
            font-weight: bold;
            color: #123B5D;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .school-subtitle {
            font-size: 12px;
            color: #64748B;
            margin-bottom: 16px;
        }

        .divider {
            width: 70%;
            margin: 0 auto 18px;
            border-top: 2px solid #B89B5E;
        }

        .certificate-title {
            font-size: 26px;
            font-weight: bold;
            color: #176B87;
            text-transform: uppercase;
            margin: 0 0 20px;
        }

        .certificate-text {
            font-size: 16px;
            line-height: 2;
            text-align: center;
        }

        .student-name {
            font-size: 21px;
            font-weight: bold;
            color: #123B5D;
        }

        .remarks {
            font-size: 13px;
            color: #475569;
            margin-top: 12px;
            line-height: 1.6;
        }

        .bottom-section {
            position: absolute;
            left: 16mm;
            right: 16mm;
            bottom: 13mm;
        }

        .bottom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bottom-table td {
            width: 33.33%;
            vertical-align: bottom;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .bottom-label {
            font-size: 10px;
            color: #64748B;
            margin-bottom: 5px;
        }

        .bottom-value {
            font-weight: bold;
            color: #123B5D;
        }

        .signature-line {
            width: 150px;
            border-top: 1px solid #123B5D;
            margin: 0 0 8px auto;
        }

        .footer {
            position: absolute;
            bottom: 5mm;
            left: 0;
            right: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #64748B;
            text-align: center;
        }
    </style>
</head>

<body>
@php
    $studentName = trim(
        ($student->first_name ?? '') . ' ' .
        ($student->middle_name ?? '') . ' ' .
        ($student->last_name ?? '')
    );

    $schoolName = $school->name ?? 'SchoolNexa';

    $issueDate = \Carbon\Carbon::parse(
        $certificate->issued_date
    )->format('d F Y');
@endphp

<div class="certificate">
    <div class="outer-border">
        <div class="inner-border">

            <div class="school-name">
                {{ $schoolName }}
            </div>

            <div class="school-subtitle">
                Official Student Certificate
            </div>

            <div class="divider"></div>

            <div class="certificate-title">
                {{ $certificate->certificate_type }}
            </div>

            <div class="certificate-text">
                This is to certify that

                <div class="student-name">
                    {{ $studentName }}
                </div>

                bearing Admission Number
                <strong>{{ $student->admission_number ?? 'N/A' }}</strong>,

                is recorded as a student of

                <strong>{{ $schoolName }}</strong>.

                <div>
                    This certificate is issued on
                    <strong>{{ $issueDate }}</strong>
                    for official purposes.
                </div>
            </div>

            @if(!empty($remarks))
                <div class="remarks">
                    <strong>Additional Details:</strong>
                    {{ $remarks }}
                </div>
            @endif

            <div class="bottom-section">
                <table class="bottom-table">
                    <tr>
                        <td style="text-align:left;">
                            <div class="bottom-label">Certificate Number</div>
                            <div class="bottom-value">
                                {{ $certificate->certificate_number }}
                            </div>
                        </td>

                        <td style="text-align:center;">
                            <div class="bottom-label">Date of Issue</div>
                            <div class="bottom-value">{{ $issueDate }}</div>
                        </td>

                        <td style="text-align:right;">
                            <div class="signature-line"></div>
                            <div class="bottom-value">
                                Authorized Signatory
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                Generated through SchoolNexa | Certificate verification:
                {{ url('/certificates/verify/' . $certificate->certificate_number) }}
            </div>

        </div>
    </div>
</div>

</body>
</html>
