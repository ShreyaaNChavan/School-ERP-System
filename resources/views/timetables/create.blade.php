@extends('layouts.schoolnexa')
@section('title', 'Create Timetable')
@section('content')
    @include('timetables._form', [
        'pageTitle' => 'Create Timetable',
        'formAction' => route('timetables.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Save as Draft',
        'timetable' => null,
    ])
@endsection
