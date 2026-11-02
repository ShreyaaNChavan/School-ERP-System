@extends('layouts.schoolnexa')
@section('title','Edit Homework')
@section('content')
@include('homework._form', [
    'pageTitle' => 'Edit Homework',
    'formAction' => route('homework.update', $homework),
    'formMethod' => 'PUT',
    'submitLabel' => 'Save Changes',
    'homework' => $homework,
])
@endsection
