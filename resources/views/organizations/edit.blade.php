@extends('layouts.schoolnexa')

@section('title', 'Edit Organization')

@section('content')
<div class="org-page">

    <div class="org-header">
        <div class="org-heading">
            <h1>Edit Organization</h1>
            <p>Update organization details and contact information.</p>
        </div>

        <a href="{{ route('organizations.index') }}" class="org-btn org-btn-light">
            ← Back to Organizations
        </a>
    </div>

    <div class="org-panel">
        <h3 class="org-panel-title">Organization Information</h3>

        <form method="POST" action="{{ route('organizations.update', $organization) }}">
            @csrf
            @method('PUT')

            @include('organizations.form')
        </form>
    </div>

</div>
@endsection