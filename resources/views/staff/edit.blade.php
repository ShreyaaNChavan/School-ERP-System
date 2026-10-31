
@extends('layouts.schoolnexa')

@section('content')

<style>
    .stf-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .stf-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .stf-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .stf-subtitle {
        font-size: 13px;
        color: #64748B;
        margin: 0;
    }

    .stf-btn {
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

    .stf-btn-outline {
        background: #fff;
        color: #25313C;
    }

    .stf-btn-outline:hover {
        background: #F1F5F9;
        color: #123B5D;
    }
</style>

<div class="stf-page">

    <div class="stf-header">
        <div>
            <h2 class="stf-title">Edit Staff Profile</h2>

            <p class="stf-subtitle">
                Update {{ $staff->full_name }}'s employment and contact information.
            </p>
        </div>

        <a href="{{ route('staff.index') }}"
           class="stf-btn stf-btn-outline">
            Back to Directory
        </a>
    </div>

    @include('staff.form')

</div>

@endsection
