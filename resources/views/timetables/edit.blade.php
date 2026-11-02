@extends('layouts.schoolnexa')
@section('title', 'Edit Timetable')
@section('content')
    @include('timetables._form', [
        'pageTitle' => 'Edit Timetable',
        'formAction' => route('timetables.update', $timetable),
        'formMethod' => 'PUT',
        'submitLabel' => 'Update Draft',
        'timetable' => $timetable,
    ])
@endsection
