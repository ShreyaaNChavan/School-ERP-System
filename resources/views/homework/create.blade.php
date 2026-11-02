@extends('layouts.schoolnexa')
@section('title','Create Homework')
@section('content')
@include('homework._form', [
    'pageTitle' => 'Create Homework',
    'formAction' => route('homework.store'),
    'formMethod' => 'POST',
    'submitLabel' => 'Create Homework',
    'homework' => null,
])
@endsection
