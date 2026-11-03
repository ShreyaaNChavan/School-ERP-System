<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FeeController extends Controller
{
    private function schoolId(): int
    {
        return (int) (session('school_id') ?: 1);
    }

    private function invoices()
    {
        return FeeInvoice::query()->where('school_id', $this->schoolId());
    }

    private function ensureInvoiceAccess(FeeInvoice $invoice): void
    {
        abort_unless((int) $invoice->school_id === $this->schoolId(), 404);
    }

    private function invoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }

    private function receiptNumber(): string
    {
        return 'RCT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }

    public function dashboard()
    {
        $base = $this->invoices();

        $stats = [
            'invoiced' => (clone $base)->sum('total_amount'),
            'collected' => (clone $base)->sum('paid_amount'),
            'outstanding' => (clone $base)->sum('balance_amount'),
            'overdue' => (clone $base)->where('balance_amount', '>', 0)
                ->whereNotNull('due_date')->whereDate('due_date', '<', today())->sum('balance_amount'),
            'invoice_count' => (clone $base)->count(),
            'paid_count' => (clone $base)->where('status', 'paid')->count(),
            'partial_count' => (clone $base)->where('status', 'partial')->count(),
            'unpaid_count' => (clone $base)->where('status', 'unpaid')->count(),
        ];

        $monthly = FeePayment::query()
            ->where('status', 'completed')
            ->whereYear('paid_at', now()->year)
            ->whereHas('invoice', fn ($q) => $q->where('school_id', $this->schoolId()))
            ->selectRaw('MONTH(paid_at) AS month_number, SUM(amount) AS total')
            ->groupByRaw('MONTH(paid_at)')
            ->pluck('total', 'month_number');

        $recentPayments = FeePayment::with(['invoice.student'])
            ->where('status', 'completed')
            ->whereHas('invoice', fn ($q) => $q->where('school_id', $this->schoolId()))
            ->latest('paid_at')->take(8)->get();

        $dueInvoices = $this->invoices()->with('student')
            ->where('balance_amount', '>', 0)
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')->take(8)->get();

        return view('fees.dashboard', compact('stats', 'monthly', 'recentPayments', 'dueInvoices'));
    }

    public function invoiceIndex(Request $request)
    {
        $query = $this->invoices()->with('student');

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhereHas('student', function ($student) use ($term) {
                        $student->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('admission_number', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->input('date_to'));
        }

        $invoices = $query->latest('invoice_date')->paginate(12)->withQueryString();
        $summary = [
            'total' => $this->invoices()->count(),
            'paid' => $this->invoices()->where('status', 'paid')->count(),
            'partial' => $this->invoices()->where('status', 'partial')->count(),
            'unpaid' => $this->invoices()->where('status', 'unpaid')->count(),
        ];

        return view('fees.invoices.index', compact('invoices', 'summary'));
    }

    private function invoiceFormData(): array
    {
        return [
            'students' => StudentProfile::where('school_id', $this->schoolId())
                ->where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get(),
            'academicYears' => AcademicYear::where('school_id', $this->schoolId())
                ->orderByDesc('id')->get(),
            'feeTypes' => FeeType::where('school_id', $this->schoolId())
                ->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    public function invoiceCreate()
    {
        $invoice = new FeeInvoice();
        return view('fees.invoices.form', array_merge($this->invoiceFormData(), compact('invoice')));
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'student_id' => [
                'required', 'integer',
                Rule::exists('student_profiles', 'id')->where('school_id', $this->schoolId()),
            ],
            'academic_year_id' => [
                'required', 'integer',
                Rule::exists('academic_years', 'id')->where('school_id', $this->schoolId()),
            ],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_type_id' => [
                'required', 'integer',
                Rule::exists('fee_types', 'id')->where('school_id', $this->schoolId())->where('is_active', true),
            ],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_amount' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function calculateTotals(array $data): array
    {
        $subtotal = round(collect($data['items'])->sum(
            fn ($item) => (float) $item['quantity'] * (float) $item['unit_amount']
        ), 2);
        $discount = round((float) ($data['discount_amount'] ?? 0), 2);
        $tax = round((float) ($data['tax_amount'] ?? 0), 2);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Discount cannot be greater than the invoice subtotal.',
            ]);
        }

        return [$subtotal, $discount, $tax, round($subtotal - $discount + $tax, 2)];
    }

    private function syncInvoiceItems(FeeInvoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $unitAmount = (float) $item['unit_amount'];
            $invoice->items()->create([
                'fee_type_id' => $item['fee_type_id'],
                'description' => trim($item['description']),
                'quantity' => $quantity,
                'unit_amount' => $unitAmount,
                'amount' => round($quantity * $unitAmount, 2),
            ]);
        }
    }

    public function invoiceStore(Request $request)
    {
        $data = $this->validateInvoice($request);
        [$subtotal, $discount, $tax, $total] = $this->calculateTotals($data);

        $invoice = DB::transaction(function () use ($data, $subtotal, $discount, $tax, $total) {
            $invoice = FeeInvoice::create([
                'school_id' => $this->schoolId(),
                'student_id' => $data['student_id'],
                'academic_year_id' => $data['academic_year_id'],
                'invoice_number' => $this->invoiceNumber(),
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => 0,
                'balance_amount' => $total,
                'status' => $total <= 0 ? 'paid' : 'unpaid',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncInvoiceItems($invoice, $data['items']);
            return $invoice;
        });

        return redirect()->route('fees.invoices.show', $invoice)
            ->with('success', 'Fee invoice created successfully.');
    }

    public function invoiceShow(FeeInvoice $invoice)
    {
        $this->ensureInvoiceAccess($invoice);
        $invoice->load(['student', 'academicYear', 'items.feeType', 'payments.receiver']);
        return view('fees.invoices.show', compact('invoice'));
    }

    public function invoiceEdit(FeeInvoice $invoice)
    {
        $this->ensureInvoiceAccess($invoice);
        if ((float) $invoice->paid_amount > 0 || $invoice->payments()->exists()) {
            return redirect()->route('fees.invoices.show', $invoice)
                ->with('error', 'An invoice with recorded payments cannot be edited.');
        }
        $invoice->load('items');
        return view('fees.invoices.form', array_merge($this->invoiceFormData(), compact('invoice')));
    }

    public function invoiceUpdate(Request $request, FeeInvoice $invoice)
    {
        $this->ensureInvoiceAccess($invoice);
        if ((float) $invoice->paid_amount > 0 || $invoice->payments()->exists()) {
            return redirect()->route('fees.invoices.show', $invoice)
                ->with('error', 'An invoice with recorded payments cannot be edited.');
        }

        $data = $this->validateInvoice($request);
        [$subtotal, $discount, $tax, $total] = $this->calculateTotals($data);

        DB::transaction(function () use ($data, $invoice, $subtotal, $discount, $tax, $total) {
            $invoice->update([
                'student_id' => $data['student_id'],
                'academic_year_id' => $data['academic_year_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'balance_amount' => $total,
                'status' => $total <= 0 ? 'paid' : 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);
            $invoice->items()->delete();
            $this->syncInvoiceItems($invoice, $data['items']);
        });

        return redirect()->route('fees.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function invoiceDelete(FeeInvoice $invoice)
    {
        $this->ensureInvoiceAccess($invoice);
        if ($invoice->payments()->exists()) {
            return back()->with('error', 'An invoice with payment records cannot be deleted.');
        }
        $invoice->delete();
        return redirect()->route('fees.invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function paymentIndex(Request $request)
    {
        $query = FeePayment::with(['invoice.student'])
            ->whereHas('invoice', fn ($q) => $q->where('school_id', $this->schoolId()));

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($q) use ($term) {
                $q->where('receipt_number', 'like', "%{$term}%")
                    ->orWhere('transaction_reference', 'like', "%{$term}%")
                    ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$term}%"));
            });
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->input('method'));
        }

        $totalCollected = (clone $query)->where('status', 'completed')->sum('amount');
        $payments = $query->latest('paid_at')->paginate(12)->withQueryString();

        return view('fees.payments.index', compact('payments', 'totalCollected'));
    }

    public function paymentCreate(Request $request)
    {
        $invoices = $this->invoices()->with('student')
            ->where('balance_amount', '>', 0)
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')->get();
        $selectedInvoice = $request->integer('invoice');

        return view('fees.payments.create', compact('invoices', 'selectedInvoice'));
    }

    public function paymentStore(Request $request)
    {
        $data = $request->validate([
            'fee_invoice_id' => ['required', 'integer', 'exists:fee_invoices,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'payment_method' => ['required', Rule::in(['cash', 'upi', 'card', 'bank_transfer', 'cheque', 'online'])],
            'transaction_reference' => ['nullable', 'string', 'max:150'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $payment = DB::transaction(function () use ($data) {
            $invoice = FeeInvoice::where('school_id', $this->schoolId())
                ->lockForUpdate()->findOrFail($data['fee_invoice_id']);

            $amount = round((float) $data['amount'], 2);
            $balance = round((float) $invoice->balance_amount, 2);

            if ($balance <= 0) {
                throw ValidationException::withMessages(['fee_invoice_id' => 'This invoice has no outstanding balance.']);
            }
            if ($amount > $balance) {
                throw ValidationException::withMessages(['amount' => 'Payment cannot exceed the outstanding invoice balance of ₹' . number_format($balance, 2) . '.']);
            }

            $payment = FeePayment::create([
                'fee_invoice_id' => $invoice->id,
                'receipt_number' => $this->receiptNumber(),
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'paid_at' => $data['paid_at'],
                'received_by' => auth()->id(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            $paid = round((float) $invoice->paid_amount + $amount, 2);
            $remaining = round(max(0, (float) $invoice->total_amount - $paid), 2);
            $invoice->update([
                'paid_amount' => $paid,
                'balance_amount' => $remaining,
                'status' => $remaining <= 0 ? 'paid' : 'partial',
            ]);

            return $payment;
        });

        return redirect()->route('fees.payments.receipt', $payment)
            ->with('success', 'Payment recorded and receipt generated successfully.');
    }

    public function receipt(FeePayment $payment)
    {
        $payment->load(['invoice.student', 'invoice.academicYear', 'receiver']);
        abort_unless($payment->invoice && (int) $payment->invoice->school_id === $this->schoolId(), 404);
        return view('fees.payments.receipt', compact('payment'));
    }

    public function feeTypesIndex()
    {
        $feeTypes = FeeType::where('school_id', $this->schoolId())->orderBy('name')->paginate(15);
        return view('fees.types.index', compact('feeTypes'));
    }

    public function feeTypesStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('fee_types', 'code')->where('school_id', $this->schoolId())],
            'category' => ['required', 'string', 'max:50'],
            'is_refundable' => ['nullable', 'boolean'],
        ]);

        FeeType::create([
            'school_id' => $this->schoolId(),
            'name' => trim($data['name']),
            'code' => strtoupper(trim($data['code'])),
            'category' => $data['category'],
            'is_refundable' => $request->boolean('is_refundable'),
            'is_active' => true,
        ]);

        return redirect()->route('fees.types.index')->with('success', 'Fee type created successfully.');
    }

    public function feeTypesToggle(FeeType $feeType)
    {
        abort_unless((int) $feeType->school_id === $this->schoolId(), 404);
        $feeType->update(['is_active' => ! $feeType->is_active]);
        return back()->with('success', 'Fee type status updated.');
    }
}
