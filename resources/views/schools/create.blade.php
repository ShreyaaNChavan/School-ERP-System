
@extends('layouts.schoolnexa')

@section('title', 'Add School')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .school-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .school-page * { box-sizing: border-box; }

    .school-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .school-heading h1 {
        color: #123B5D;
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .school-heading p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .school-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 9px;
        font: 600 13px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .school-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .school-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .school-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    .school-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 20px;
    }

    .school-panel-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 20px;
    }

    @media (max-width: 640px) {
        .school-heading h1 { font-size: 21px; }
        .school-panel { padding: 16px; }
    }
</style>

<div class="school-page">

    <div class="school-header">
        <div class="school-heading">
            <h1>Add School</h1>
            <p>Register a new school under an organization.</p>
        </div>

        <a href="{{ route('schools.index') }}" class="school-btn school-btn-light">
            ← Back to Schools
        </a>
    </div>

    <div class="school-panel">
        <h3 class="school-panel-title">School Information</h3>

        <form method="POST" action="{{ route('schools.store') }}">
            @csrf

            @include('schools.form')
        </form>
    </div>

</div>

@endsection
