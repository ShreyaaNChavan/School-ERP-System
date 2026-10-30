
@extends('layouts.schoolnexa')

@section('content')

<style>
    .student-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .student-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .student-page-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0;
    }

    .student-page-description {
        font-size: 13px;
        color: #64748B;
        margin-top: 5px;
    }

    .student-breadcrumb {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 8px;
    }

    .student-breadcrumb a {
        color: #176B87;
        text-decoration: none;
    }

    .student-alert {
        padding: 13px 16px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 13px;
    }

    .student-alert-error {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #B91C1C;
    }
</style>

<div class="student-page">

    <div class="student-page-header">
        <div>
            <div class="student-breadcrumb">
                <a href="{{ route('students.index') }}">Students</a>
                / Edit Student
            </div>

            <h1 class="student-page-title">Edit Student</h1>

            <div class="student-page-description">
                Update the personal, admission and academic details of
                {{ $student->full_name }}.
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="student-alert student-alert-error">
            <strong>Please correct the following errors:</strong>
            <ul style="margin: 8px 0 0 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('students.form')

</div>

@endsection
