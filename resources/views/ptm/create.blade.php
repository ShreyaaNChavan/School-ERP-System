
@extends('layouts.schoolnexa')

@section('content')

<div class="sn-page ptm-page">

    @include('ptm._style')

    <div class="ptm-head">
        <div>
            <h1>Schedule PTM</h1>
            <div class="ptm-sub">
                Create a parent–teacher meeting request or confirmed appointment
            </div>
        </div>

        <div class="ptm-actions">
            <a class="ptm-btn" href="{{ route('ptm.dashboard') }}">
                ← Back to PTM Dashboard
            </a>
        </div>
    </div>

    <div class="ptm-panel">

        <form method="POST" action="{{ route('ptm.store') }}">

            @csrf

            {{-- PTM form fields --}}
            @include('ptm._form')

            <div class="ptm-actions" style="margin-top: 18px;">

                <button class="ptm-btn primary" type="submit">
                    Save Meeting
                </button>

                <a class="ptm-btn" href="{{ route('ptm.dashboard') }}">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

@endsection
