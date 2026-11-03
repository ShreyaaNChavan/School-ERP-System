@extends('layouts.schoolnexa')
@section('content')
@include('fees.partials.styles')
<style>
    .payment-page{width:100%;max-width:100%}
    .payment-card{width:100%;max-width:100%;margin:0}
    .payment-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px 16px}
    .payment-actions{display:flex;justify-content:flex-end;align-items:center;gap:9px;margin-top:20px;padding-top:17px;border-top:1px solid #E2E8F0}
    .payment-summary{display:none;margin-top:13px;padding:15px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px}
    .payment-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
    .payment-summary-label{font-size:10px;color:#64748B;font-weight:700;margin-bottom:5px}
    .payment-summary-value{font-size:13px;color:#123B5D;font-weight:700;overflow-wrap:anywhere}
    .fee-btn:disabled{opacity:.55;cursor:not-allowed}
    @media(max-width:700px){.payment-grid{grid-template-columns:1fr}.payment-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.payment-actions{flex-direction:column-reverse;align-items:stretch}.payment-actions .fee-btn{width:100%}}
</style>
<div class="fee-page payment-page">
    <div class="fee-head">
        <div><h1>Record Fee Payment</h1><p>Record a payment against an outstanding student fee invoice.</p></div>
        <a href="{{ route('fees.payments.index') }}" class="fee-btn">← Back to Payments</a>
    </div>

    @if($errors->any())<div class="fee-alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('fees.payments.store') }}" class="fee-card payment-card">
        @csrf
        <div style="margin-bottom:18px"><h3 class="fee-section-title">Payment Information</h3><div class="fee-subtitle">Choose an invoice and enter the transaction details.</div></div>

        <div class="fee-field">
            <label class="fee-label">Outstanding Invoice <span style="color:#DC2626">*</span></label>
            <select class="fee-select" name="fee_invoice_id" id="invoiceSelect" required>
                <option value="">Select invoice</option>
                @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}" data-balance="{{ $invoice->balance_amount }}" data-total="{{ $invoice->total_amount }}" data-paid="{{ $invoice->paid_amount }}" data-student="{{ $invoice->student?->first_name }} {{ $invoice->student?->last_name }}" data-number="{{ $invoice->invoice_number }}" @selected(old('fee_invoice_id',$selectedInvoice)==$invoice->id)>
                        {{ $invoice->invoice_number }} — {{ $invoice->student?->first_name }} {{ $invoice->student?->last_name }} — Balance ₹{{ number_format($invoice->balance_amount,2) }}
                    </option>
                @endforeach
            </select>
            <div id="invoiceHint" class="fee-muted">
                @if($invoices->isEmpty())
                    No outstanding invoices are available. <a href="{{ route('fees.invoices.create') }}" style="color:#176B87;font-weight:700">Create an invoice</a> first.
                @else
                    Select an invoice to see its amount and remaining balance.
                @endif
            </div>
            <div class="payment-summary" id="invoiceSummary">
                <div style="font-size:12px;font-weight:700;color:#123B5D;margin-bottom:12px">Selected Invoice Summary</div>
                <div class="payment-summary-grid">
                    <div><div class="payment-summary-label">INVOICE NUMBER</div><div class="payment-summary-value" id="sumNumber">—</div></div>
                    <div><div class="payment-summary-label">STUDENT</div><div class="payment-summary-value" id="sumStudent">—</div></div>
                    <div><div class="payment-summary-label">INVOICE TOTAL</div><div class="payment-summary-value" id="sumTotal">₹0.00</div></div>
                    <div><div class="payment-summary-label">ALREADY PAID</div><div class="payment-summary-value" id="sumPaid">₹0.00</div></div>
                </div>
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;border-top:1px solid #E2E8F0;margin-top:13px;padding-top:12px"><span style="font-size:12px;font-weight:650;color:#475569">Remaining Balance</span><strong id="sumBalance" style="font-size:18px;color:#DC2626">₹0.00</strong></div>
            </div>
        </div>

        <div style="border-top:1px solid #E2E8F0;padding-top:18px;margin:18px 0"><h3 class="fee-section-title">Transaction Details</h3><div class="fee-subtitle">Payment amount, method and reference information.</div></div>

        <div class="payment-grid">
            <div class="fee-field"><label class="fee-label">Payment Amount (₹) <span style="color:#DC2626">*</span></label><input class="fee-input" type="number" name="amount" id="amountInput" step="0.01" min="0.01" value="{{ old('amount') }}" placeholder="Enter payment amount" required><div class="fee-muted">Cannot exceed the outstanding balance.</div></div>
            <div class="fee-field"><label class="fee-label">Payment Method <span style="color:#DC2626">*</span></label><select class="fee-select" name="payment_method" required>@foreach(['cash'=>'Cash','upi'=>'UPI','card'=>'Card','bank_transfer'=>'Bank Transfer','cheque'=>'Cheque','online'=>'Online'] as $value=>$label)<option value="{{ $value }}" @selected(old('payment_method','cash')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="fee-field"><label class="fee-label">Payment Date & Time <span style="color:#DC2626">*</span></label><input class="fee-input" type="datetime-local" name="paid_at" value="{{ old('paid_at',now()->format('Y-m-d\\TH:i')) }}" required></div>
            <div class="fee-field"><label class="fee-label">Transaction Reference</label><input class="fee-input" name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="UTR / UPI reference / cheque number"></div>
        </div>

        <div class="fee-field" style="margin-top:4px"><label class="fee-label">Payment Notes</label><textarea class="fee-textarea" name="notes" rows="3" placeholder="Enter additional payment details...">{{ old('notes') }}</textarea></div>

        <div class="payment-actions">
            <a href="{{ route('fees.payments.index') }}" class="fee-btn">Cancel</a>
            <button type="submit" class="fee-btn fee-btn-primary" id="savePayment" {{ $invoices->isEmpty() ? 'disabled' : '' }}>Save Payment & Generate Receipt</button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const select=document.getElementById('invoiceSelect'), amount=document.getElementById('amountInput');
    const summary=document.getElementById('invoiceSummary'), hint=document.getElementById('invoiceHint'), save=document.getElementById('savePayment');
    const money=v=>'₹'+Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
    function refresh(){
        const option=select.options[select.selectedIndex];
        if(!option||!option.value){summary.style.display='none';amount.removeAttribute('max');save.disabled=select.options.length<=1;return;}
        const balance=Number(option.dataset.balance||0);
        document.getElementById('sumNumber').textContent=option.dataset.number||'—';
        document.getElementById('sumStudent').textContent=(option.dataset.student||'').trim()||'—';
        document.getElementById('sumTotal').textContent=money(option.dataset.total);
        document.getElementById('sumPaid').textContent=money(option.dataset.paid);
        document.getElementById('sumBalance').textContent=money(balance);
        summary.style.display='block';hint.textContent='Outstanding balance: '+money(balance);
        amount.max=balance;save.disabled=balance<=0;
    }
    select.addEventListener('change',refresh);refresh();
});
</script>
@endsection
