
@extends('layouts.schoolnexa')

@section('title', 'Add Campus')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .campus-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .campus-page * { box-sizing: border-box; }

    .campus-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .campus-heading h1 {
        color: #123B5D;
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .campus-heading p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .campus-btn {
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

    .campus-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .campus-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .campus-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    .campus-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 20px;
    }

    .campus-panel-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 20px;
    }

    @media (max-width: 640px) {
        .campus-heading h1 { font-size: 21px; }
        .campus-panel { padding: 16px; }
    }
</style>

<div class="campus-page">

    <div class="campus-header">
        <div class="campus-heading">
            <h1>Add Campus</h1>
            <p>Register a new campus under an existing school.</p>
        </div>

        <a href="{{ route('campuses.index') }}" class="campus-btn campus-btn-light">
            ← Back to Campuses
        </a>
    </div>

    <div class="campus-panel">
        <h3 class="campus-panel-title">Campus Information</h3>

        <form method="POST" action="{{ route('campuses.store') }}">
            @csrf
            @include('campuses.form')
        </form>
    </div>

</div>

@endsection
