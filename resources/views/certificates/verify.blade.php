
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate Verification | SchoolNexa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#F1F5F9;font-family:Inter,sans-serif;color:#25313C;min-height:100vh}
        .topbar{height:68px;background:#123B5D;display:flex;align-items:center;padding:0 7%;color:#fff}
        .brand{font-size:20px;font-weight:800;letter-spacing:-.5px}
        .brand span{color:#5EEAD4}
        .container{max-width:760px;margin:65px auto;padding:0 20px}
        .intro{text-align:center;margin-bottom:28px}
        .intro h1{font-size:27px;color:#123B5D;margin:0 0 10px;font-weight:750}
        .intro p{font-size:13px;color:#64748B;line-height:1.7;margin:0}
        .panel{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:28px;box-shadow:0 8px 25px rgba(15,23,42,.04)}
        .label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:8px}
        .search-row{display:flex;gap:10px}
        .search-row input{flex:1;min-width:0;height:44px;border:1px solid #CBD5E1;border-radius:9px;padding:0 13px;font:13px Inter,sans-serif}
        .search-row input:focus{outline:none;border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.1)}
        .btn{height:44px;padding:0 20px;border:0;border-radius:9px;background:#123B5D;color:white;font:600 13px Inter,sans-serif;cursor:pointer}
        .btn:hover{background:#176B87}
        .result{margin-top:24px;border-radius:12px;padding:18px;border:1px solid}
        .result.valid{background:#F0FDF4;border-color:#BBF7D0}
        .result.invalid{background:#FEF2F2;border-color:#FECACA}
        .result-title{font-size:15px;font-weight:750;margin-bottom:6px}
        .valid .result-title{color:#166534}
        .invalid .result-title{color:#991B1B}
        .result-description{font-size:12px;color:#475569;line-height:1.6}
        .details{margin-top:16px;border-top:1px solid #E2E8F0;padding-top:15px}
        .detail-row{display:flex;justify-content:space-between;gap:15px;padding:10px 0;border-bottom:1px solid #E2E8F0;font-size:12px}
        .detail-row:last-child{border-bottom:0}
        .detail-label{color:#64748B}
        .detail-value{font-weight:650;color:#123B5D;text-align:right}
        .footer{text-align:center;color:#94A3B8;font-size:11px;margin-top:28px}
        .error{font-size:12px;color:#DC2626;margin-top:8px}
        @media(max-width:520px){.container{margin:35px auto}.panel{padding:20px}.search-row{flex-direction:column}.btn{width:100%}.detail-row{flex-direction:column;gap:5px}.detail-value{text-align:left}}
    </style>
</head>

<body>
    <header class="topbar">
        <div class="brand">School<span>Nexa</span></div>
    </header>

    <main class="container">
        <div class="intro">
            <h1>Certificate Verification</h1>
            <p>
                Verify the authenticity of a certificate issued by a school
                using its unique certificate number.
            </p>
        </div>

        <div class="panel">
            <form method="GET" action="{{ route('certificates.verify') }}">
                <label class="label" for="certificate_number">
                    Certificate Number
                </label>

                <div class="search-row">
                    <input
                        type="text"
                        id="certificate_number"
                        name="certificate_number"
                        value="{{ request('certificate_number', $certificateNumber ?? '') }}"
                        placeholder="Example: CASPA-2026-AB12CD34"
                        maxlength="255"
                        required
                    >

                    <button type="submit" class="btn">
                        Verify Certificate
                    </button>
                </div>
            </form>

            @if(isset($searched) && $searched)
                @if($valid)
                    <div class="result valid">
                        <div class="result-title">Certificate Record Found</div>
                        <div class="result-description">
                            A matching certificate record exists in the SchoolNexa database.
                        </div>

                        <div class="details">
                            <div class="detail-row">
                                <span class="detail-label">Certificate Number</span>
                                <span class="detail-value">{{ $record->certificate_number }}</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Certificate Type</span>
                                <span class="detail-value">{{ $record->certificate_type }}</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Student Name</span>
                                <span class="detail-value">
                                    {{ $student ? trim($student->first_name.' '.($student->middle_name ?? '').' '.$student->last_name) : 'Unavailable' }}
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">School</span>
                                <span class="detail-value">
                                    {{ \Illuminate\Support\Facades\DB::table('schools')->where('id', $record->school_id)->value('name') ?? 'School' }}
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Issue Date</span>
                                <span class="detail-value">
                                    {{ \Carbon\Carbon::parse($record->issued_date)->format('d F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="result invalid">
                        <div class="result-title">Certificate Not Found</div>
                        <div class="result-description">
                            No certificate record matches
                            <strong>{{ $certificateNumber }}</strong>.
                            Please check the certificate number and try again.
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div class="footer">
            © {{ date('Y') }} SchoolNexa. Certificate Verification Portal.
        </div>
    </main>
</body>
</html>
