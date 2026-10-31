
@extends('layouts.schoolnexa')

@section('content')
<div class="cv-page">

    <div class="cv-header">
        <div>
            <h2>Edit Curriculum</h2>
            <p>Update {{ $curriculum->title }}</p>
        </div>
        <a href="{{ route('curricula.show',$curriculum) }}" class="cv-outline">Back to Curriculum</a>
    </div>

    <form action="{{ route('curricula.update',$curriculum) }}" method="POST">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="cv-alert error">
                <strong>Please correct the following errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="cv-card">
            <h3>Curriculum Information</h3>
            <p>Modify the curriculum details.</p>

            @include('curricula.form')
        </div>

        <div class="cv-actions">
            <a href="{{ route('curricula.show',$curriculum) }}" class="cv-outline">Cancel</a>
            <button type="submit" class="cv-primary">Update Curriculum</button>
        </div>
    </form>
</div>

<style>
.cv-page{max-width:1100px;margin:auto}
.cv-header{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:22px}
.cv-header h2{font-size:22px;color:#123B5D;font-weight:700;margin:0}
.cv-header p{font-size:13px;color:#64748B;margin:6px 0 0}
.cv-card{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:24px}
.cv-card h3{font-size:16px;color:#123B5D;margin:0 0 5px}
.cv-card>p{font-size:13px;color:#64748B;margin:0 0 23px}
.cv-outline,.cv-primary{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer}
.cv-outline{background:#fff;border:1px solid #CBD5E1;color:#123B5D}
.cv-primary{background:#123B5D;border:1px solid #123B5D;color:#fff}
.cv-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}
.cv-alert{padding:13px 16px;border-radius:9px;margin-bottom:18px;font-size:13px}
.cv-alert.error{background:#FEE2E2;color:#991B1B}
.cv-alert ul{margin:7px 0 0;padding-left:20px}
</style>
@endsection
