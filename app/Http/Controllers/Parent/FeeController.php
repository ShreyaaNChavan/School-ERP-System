<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class FeeController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();
        $ids = $context['childIds'] ?: [0];

        $invoices = DB::table('fee_invoices as fi')
            ->join('student_profiles as sp', 'sp.id', '=', 'fi.student_id')
            ->whereIn('fi.student_id', $ids)
            ->select('fi.*', 'sp.first_name', 'sp.last_name', 'sp.admission_number')
            ->orderByDesc('fi.invoice_date')
            ->paginate(15);

        $totals = DB::table('fee_invoices')
            ->whereIn('student_id', $ids)
            ->selectRaw('COALESCE(SUM(total_amount),0) as total')
            ->selectRaw('COALESCE(SUM(paid_amount),0) as paid')
            ->selectRaw('COALESCE(SUM(balance_amount),0) as balance')
            ->first();

        return view('parent.fees.index', compact('invoices', 'totals'));
    }

    public function show(int $invoice)
    {
        $context = $this->parentContext();

        $item = DB::table('fee_invoices as fi')
            ->join('student_profiles as sp', 'sp.id', '=', 'fi.student_id')
            ->where('fi.id', $invoice)
            ->whereIn('fi.student_id', $context['childIds'] ?: [0])
            ->select('fi.*', 'sp.first_name', 'sp.last_name', 'sp.admission_number')
            ->first();

        abort_unless($item, 404);

        $items = DB::table('fee_invoice_items as fii')
            ->leftJoin('fee_types as ft', 'ft.id', '=', 'fii.fee_type_id')
            ->where('fii.fee_invoice_id', $invoice)
            ->select('fii.*', 'ft.name as fee_type_name')
            ->get();

        $payments = DB::table('fee_payments')
            ->where('fee_invoice_id', $invoice)
            ->where('status', 'completed')
            ->orderByDesc('paid_at')
            ->get(['receipt_number', 'amount', 'payment_method', 'paid_at', 'status']);

        return view('parent.fees.show', compact('item', 'items', 'payments'));
    }
}
