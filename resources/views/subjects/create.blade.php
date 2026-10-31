@extends('layouts.schoolnexa')

@section('content')
    <div style="max-width:1000px;margin:auto;">

        <div
            style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;gap:12px;flex-wrap:wrap;">
            <div>
                <h2 style="font-size:22px;font-weight:700;color:#123B5D;margin:0;">Add Subject</h2>
                <p style="font-size:13px;color:#64748B;margin:6px 0 0;">Create a new academic subject</p>
            </div>

            <a href="{{ route('subjects.index') }}" class="subject-back">Back to Directory</a>
        </div>

        <form action="{{ route('subjects.store') }}" method="POST">
            @csrf
            @if($errors->any())
                <div
                    style="background:#FEE2E2;color:#991B1B;padding:14px 16px;border-radius:9px;margin-bottom:18px;font-size:13px;">
                    <strong>Please correct the following errors:</strong>
                    <ul style="margin:8px 0 0;padding-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="subject-panel">
                <h3>Subject Information</h3>
                <p>Enter the basic details of the subject.</p>

                @include('subjects.form')
            </div>

            <div class="subject-actions">
                <a href="{{ route('subjects.index') }}" class="subject-cancel">Cancel</a>
                <button type="submit" class="subject-primary">Save Subject</button>
            </div>
        </form>

    </div>

    <style>
        .subject-panel {
            background: #fff;
            border: 1px solid #CBD5E1;
            border-radius: 16px;
            padding: 24px;
        }

        .subject-panel h3 {
            font-size: 16px;
            color: #123B5D;
            margin: 0 0 5px;
            font-weight: 700;
        }

        .subject-panel>p {
            font-size: 13px;
            color: #64748B;
            margin: 0 0 24px;
        }

        .subject-back,
        .subject-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            padding: 0 16px;
            background: #fff;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            color: #123B5D;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .subject-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .subject-primary {
            height: 38px;
            padding: 0 18px;
            background: #123B5D;
            color: #fff;
            border: 1px solid #123B5D;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .subject-primary:hover {
            background: #176B87;
        }

        .subject-cancel:hover,
        .subject-back:hover {
            background: #F8FAFC;
        }
    </style>
@endsection