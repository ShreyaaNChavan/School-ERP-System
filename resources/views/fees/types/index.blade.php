@extends('layouts.schoolnexa')
@section('content')
@include('fees.partials.styles')
<div class="fee-page">
    <div class="fee-head">
        <div>
            <h1>Fee Types</h1>
            <p>Configure the fee categories used when creating student invoices.</p>
        </div>
        <div class="fee-actions">
            <a href="{{ route('fees.invoices.create') }}" class="fee-btn">Back to Invoice</a>
            <a href="{{ route('fees.invoices.index') }}" class="fee-btn">All Invoices</a>
        </div>
    </div>

    @if($errors->any())
        <div class="fee-alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <div class="fee-grid" style="grid-template-columns:minmax(280px,.8fr) minmax(0,1.5fr);align-items:start">
        <form method="POST" action="{{ route('fees.types.store') }}" class="fee-card">
            @csrf
            <h3 class="fee-section-title">Add Fee Type</h3>
            <div class="fee-subtitle" style="margin-bottom:18px">Create a fee category for this school.</div>
            <div class="fee-field">
                <label class="fee-label">Fee Type Name *</label>
                <input class="fee-input" name="name" value="{{ old('name') }}" placeholder="e.g. Tuition Fee" required>
            </div>
            <div class="fee-field">
                <label class="fee-label">Unique Code *</label>
                <input class="fee-input" name="code" value="{{ old('code') }}" placeholder="e.g. TUITION" required>
            </div>
            <div class="fee-field">
                <label class="fee-label">Category *</label>
                <select class="fee-select" name="category" required>
                    @foreach(['tuition'=>'Tuition','admission'=>'Admission','transport'=>'Transport','hostel'=>'Hostel',' examination'=>'Examination','activity'=>'Activity','library'=>'Library','other'=>'Other'] as $value=>$label)
                        <option value="{{ trim($value) }}" @selected(old('category','tuition')===trim($value))>{{ trim($label) }}</option>
                    @endforeach
                </select>
            </div>
            <label style="display:flex;align-items:center;gap:9px;font-size:12px;color:#475569;margin:5px 0 18px">
                <input type="checkbox" name="is_refundable" value="1" @checked(old('is_refundable'))>
                This fee type is refundable
            </label>
            <button class="fee-btn fee-btn-primary" type="submit">Save Fee Type</button>
        </form>

        <div class="fee-card">
            <h3 class="fee-section-title">Configured Fee Types</h3>
            <div class="fee-subtitle">Only active fee types appear in the invoice form.</div>
            <div class="fee-table-wrap">
                <table class="fee-table" style="min-width:550px">
                    <thead><tr><th>NAME</th><th>CODE</th><th>CATEGORY</th><th>REFUNDABLE</th><th>STATUS</th><th>ACTION</th></tr></thead>
                    <tbody>
                    @forelse($feeTypes as $type)
                        <tr>
                            <td class="fee-person">{{ $type->name }}</td>
                            <td>{{ $type->code }}</td>
                            <td>{{ ucfirst($type->category) }}</td>
                            <td>{{ $type->is_refundable ? 'Yes' : 'No' }}</td>
                            <td><span class="fee-badge {{ $type->is_active ? 'fee-paid' : 'fee-unpaid' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('fees.types.toggle',$type) }}">
                                    @csrf @method('PATCH')
                                    <button class="fee-btn" type="submit">{{ $type->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="fee-empty">No fee types configured yet. Add the first fee type using the form.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="fee-pagination">{{ $feeTypes->links() }}</div>
        </div>
    </div>
</div>
@endsection
