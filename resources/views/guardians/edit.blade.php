
@extends('layouts.schoolnexa')

@section('content')

<style>
    .gdn-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .gdn-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .gdn-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .gdn-subtitle {
        font-size: 13px;
        color: #64748B;
        margin: 0;
    }

    .gdn-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #CBD5E1;
        cursor: pointer;
        white-space: nowrap;
    }

    .gdn-btn-outline {
        background: #fff;
        color: #25313C;
    }

    .gdn-btn-outline:hover {
        background: #F1F5F9;
        color: #123B5D;
    }
</style>

<div class="gdn-page">

    <div class="gdn-header">
        <div>
            <h2 class="gdn-title">Edit Parent / Guardian</h2>
            <p class="gdn-subtitle">
                Update {{ $guardian->full_name }}'s personal information,
                contact details and linked students.
            </p>
        </div>

        <a href="{{ route('guardians.index') }}"
           class="gdn-btn gdn-btn-outline">
            Back to Directory
        </a>
    </div>

    @include('guardians.form')

</div>

@endsection
