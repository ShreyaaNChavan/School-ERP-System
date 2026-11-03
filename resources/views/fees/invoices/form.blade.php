@extends('layouts.schoolnexa')
@section('content')
@include('fees.partials.styles')
<div class="fee-page"><div class="fee-head"><div><h1>{{ $invoice->exists?'Edit Fee Invoice':'Create Fee Invoice' }}</h1><p>Enter student details and add fee line items.</p></div><div class="fee-actions"><a class="fee-btn" href="{{ route('fees.dashboard') }}">← Fee Dashboard</a><a class="fee-btn" href="{{ route('fees.invoices.index') }}">Back to Invoices</a></div></div>
@if($errors->any())<div class="fee-alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $invoice->exists?route('fees.invoices.update',$invoice):route('fees.invoices.store') }}" class="fee-card">@csrf @if($invoice->exists)@method('PUT')@endif
<h3 class="fee-section-title" style="margin-bottom:15px">Invoice Information</h3>
@if($students->isEmpty())
<div class="fee-alert">No active students were found for this school. Add or activate a student before creating an invoice.</div>
@endif
@if($academicYears->isEmpty())
<div class="fee-alert">No academic year is configured for this school. Configure an academic year before creating an invoice.</div>
@endif<div class="fee-form-grid">
<div><label class="fee-label">Student *</label><select class="fee-select" name="student_id" required><option value="">Select student</option>@foreach($students as $s)<option value="{{ $s->id }}" @selected(old('student_id',$invoice->student_id)==$s->id)>{{ $s->admission_number }} — {{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div>
<div><label class="fee-label">Academic Year *</label><select class="fee-select" name="academic_year_id" required><option value="">Select academic year</option>@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id',$invoice->academic_year_id)==$y->id)>{{ $y->name }}</option>@endforeach</select></div>
<div><label class="fee-label">Invoice Date *</label><input class="fee-input" type="date" name="invoice_date" value="{{ old('invoice_date',$invoice->invoice_date?->format('Y-m-d')??today()->format('Y-m-d')) }}" required></div>
<div><label class="fee-label">Due Date</label><input class="fee-input" type="date" name="due_date" value="{{ old('due_date',$invoice->due_date?->format('Y-m-d')) }}"></div>
<div><label class="fee-label">Discount Amount (₹)</label><input class="fee-input" type="number" step=".01" min="0" name="discount_amount" value="{{ old('discount_amount',$invoice->discount_amount??0) }}"></div>
<div><label class="fee-label">Tax Amount (₹)</label><input class="fee-input" type="number" step=".01" min="0" name="tax_amount" value="{{ old('tax_amount',$invoice->tax_amount??0) }}"></div></div>
@php($rows=old('items',$invoice->exists?$invoice->items->map(fn($i)=>['fee_type_id'=>$i->fee_type_id,'description'=>$i->description,'quantity'=>$i->quantity,'unit_amount'=>$i->unit_amount])->toArray():[['fee_type_id'=>'','description'=>'Tuition Fee','quantity'=>1,'unit_amount'=>'']]))
<div style="display:flex;justify-content:space-between;align-items:center;margin:24px 0 12px;gap:12px;flex-wrap:wrap"><div><h3 class="fee-section-title">Invoice Items</h3><div class="fee-subtitle">Add one or more fee components to this invoice.</div></div><div style="display:flex;gap:8px;align-items:center"><a class="fee-btn" href="{{ route('fees.types.index') }}">Manage Fee Types</a><button type="button" class="fee-btn" id="addItem">＋ Add Item</button></div></div>
@if($feeTypes->isEmpty())
<div class="fee-alert">No active fee types are configured for this school. <a href="{{ route('fees.types.index') }}" style="font-weight:700;color:#123B5D;text-decoration:underline">Create a fee type</a> before creating an invoice.</div>
@endif
<div style="overflow-x:auto"><table class="fee-table" style="min-width:700px"><thead><tr><th>FEE TYPE</th><th>DESCRIPTION</th><th>QUANTITY</th><th>UNIT AMOUNT (₹)</th><th></th></tr></thead><tbody id="itemsBody">
@foreach($rows as $n=>$row)<tr class="item-row"><td><select class="fee-select" name="items[{{ $n }}][fee_type_id]" required><option value="">Select type</option>@foreach($feeTypes as $t)<option value="{{ $t->id }}" @selected(($row['fee_type_id']??'')==$t->id)>{{ $t->name }}</option>@endforeach</select></td><td><input class="fee-input" name="items[{{ $n }}][description]" value="{{ $row['description']??'' }}" required></td><td><input class="fee-input" type="number" step=".01" min=".01" name="items[{{ $n }}][quantity]" value="{{ $row['quantity']??1 }}" required></td><td><input class="fee-input" type="number" step=".01" min="0" name="items[{{ $n }}][unit_amount]" value="{{ $row['unit_amount']??'' }}" required></td><td><button type="button" class="fee-btn fee-btn-danger remove-item">Remove</button></td></tr>@endforeach
</tbody></table></div>
<div class="fee-field" style="margin-top:18px"><label class="fee-label">Notes</label><textarea class="fee-textarea" name="notes" rows="3">{{ old('notes',$invoice->notes) }}</textarea></div>
<div style="display:flex;justify-content:flex-end;gap:8px"><a class="fee-btn" href="{{ route('fees.invoices.index') }}">Cancel</a><button class="fee-btn fee-btn-primary" {{ ($feeTypes->isEmpty() || $students->isEmpty() || $academicYears->isEmpty()) ? 'disabled' : '' }}>{{ $invoice->exists?'Save Changes':'Create Invoice' }}</button></div>
</form></div>
<script>
(()=>{let n={{ count($rows) }};const body=document.getElementById('itemsBody');const types=@json($feeTypes->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values());document.getElementById('addItem').addEventListener('click',()=>{let tr=document.createElement('tr');tr.className='item-row';let opts='<option value="">Select type</option>'+types.map(t=>`<option value="${t.id}">${t.name}</option>`).join('');tr.innerHTML=`<td><select class="fee-select" name="items[${n}][fee_type_id]" required>${opts}</select></td><td><input class="fee-input" name="items[${n}][description]" required></td><td><input class="fee-input" type="number" step=".01" min=".01" name="items[${n}][quantity]" value="1" required></td><td><input class="fee-input" type="number" step=".01" min="0" name="items[${n}][unit_amount]" required></td><td><button type="button" class="fee-btn fee-btn-danger remove-item">Remove</button></td>`;body.appendChild(tr);n++;});body.addEventListener('click',e=>{if(e.target.classList.contains('remove-item')&&body.querySelectorAll('.item-row').length>1)e.target.closest('tr').remove();});})();
</script>
@endsection