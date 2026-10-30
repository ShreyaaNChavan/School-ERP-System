@extends('layouts.schoolnexa')

@section('title', 'Add Organization')

@section('content')
<div class="org-page">

    <div class="org-header">
        <div class="org-heading">
            <h1>Add Organization</h1>
            <p>Register a new organization in SchoolNexa.</p>
        </div>

        <a href="{{ route('organizations.index') }}" class="org-btn org-btn-light">
            ← Back to Organizations
        </a>
    </div>

    <div class="org-panel">
        <h3 class="org-panel-title">Organization Information</h3>

        <form method="POST" action="{{ route('organizations.store') }}">
            @csrf
            @include('organizations.form')
        </form>
    </div>

</div>
@endsection