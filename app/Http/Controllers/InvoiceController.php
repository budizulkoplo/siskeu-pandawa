<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Item;
use App\Models\Setting;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer')->latest('invoice_date')->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        $invoices = $query->paginate(25);
        $metrics = [
            'unpaid' => Invoice::whereIn('status', ['sent', 'unpaid', 'partially_paid'])->sum(DB::raw('grand_total - paid_total')),
            'overdue' => Invoice::where('due_date', '<', today())->whereColumn('paid_total', '<', 'grand_total')->sum(DB::raw('grand_total - paid_total')),
            'due_soon' => Invoice::whereBetween('due_date', [today(), today()->addDays(7)])->whereColumn('paid_total', '<', 'grand_total')->sum(DB::raw('grand_total - paid_total')),
            'paid_month' => Invoice::where('status', 'paid')->whereBetween('invoice_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('grand_total'),
        ];
        return view('invoice.index', compact('invoices', 'metrics'));
    }

    public function create()
    {
        return view('invoice.form', ['customers' => Customer::where('status', 'aktif')->orderBy('name')->get(), 'items' => Item::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request, AuditService $audit)
    {
        $request->validate(['invoice_date' => 'required|date', 'customer_id' => 'required|exists:customers,id', 'items' => 'required|array|min:1', 'items.*.description' => 'required', 'items.*.quantity' => 'required|numeric|min:.001', 'items.*.unit_price' => 'required|numeric|min:0']);
        $invoice = DB::transaction(function () use ($request) {
            $lines = collect($request->input('items'))->map(function ($line) {
                $base = (float) $line['quantity'] * (float) $line['unit_price'];
                $discount = (float) ($line['discount'] ?? 0);
                $tax = (float) ($line['tax'] ?? 0);
                return [...$line, 'discount' => $discount, 'tax' => $tax, 'total' => $base - $discount + $tax];
            });
            $subtotal = $lines->sum(fn ($line) => (float) $line['quantity'] * (float) $line['unit_price']);
            $discount = $request->float('discount');
            $tax = $request->float('tax');
            $grandTotal = $subtotal - $discount + $tax;
            $invoice = Invoice::create(['invoice_no' => 'INV-' . now()->format('YmdHis'), 'invoice_date' => $request->date('invoice_date'), 'due_date' => $request->date('due_date'), 'customer_id' => $request->integer('customer_id'), 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'grand_total' => $grandTotal, 'payment_term' => $request->string('payment_term'), 'status' => $request->string('status', 'sent'), 'notes' => $request->string('notes'), 'terbilang' => $this->terbilang($grandTotal), 'created_by' => auth()->id()]);
            foreach ($lines as $line) $invoice->items()->create($line + ['item_id' => $line['item_id'] ?? null, 'unit' => $line['unit'] ?? null]);
            AccountReceivable::create(['invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'amount' => $grandTotal, 'due_date' => $invoice->due_date, 'status' => $grandTotal > 0 ? 'unpaid' : 'paid']);
            return $invoice;
        });
        $audit->record($invoice, 'created', null, $invoice->load('items')->toArray(), 'Invoice dibuat.');
        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_no} berhasil dibuat.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.item', 'payments', 'receivable', 'deliveryOrders']);
        return view('invoice.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.item']);
        return Pdf::loadView('invoice.print', ['invoice' => $invoice, 'setting' => Setting::first() ?: new Setting()])->setPaper('a4')->download($invoice->invoice_no . '.pdf');
    }

    public function payment(Request $request, Invoice $invoice, AuditService $audit)
    {
        $request->validate(['amount' => 'required|numeric|min:.01', 'payment_date' => 'required|date', 'method' => 'required']);
        $outstanding = (float) $invoice->grand_total - (float) $invoice->paid_total;
        if ($request->float('amount') > $outstanding) throw ValidationException::withMessages(['amount' => 'Nominal pembayaran melebihi sisa piutang.']);
        DB::transaction(function () use ($request, $invoice) {
            $cash = CashTransaction::create(['transaction_no' => 'RCV-' . now()->format('YmdHis'), 'transaction_date' => $request->date('payment_date'), 'direction' => 'in', 'amount' => $request->float('amount'), 'description' => 'Pembayaran invoice ' . $invoice->invoice_no, 'reference_type' => Invoice::class, 'reference_id' => $invoice->id, 'created_by' => auth()->id()]);
            InvoicePayment::create(['invoice_id' => $invoice->id, 'payment_date' => $request->date('payment_date'), 'amount' => $request->float('amount'), 'method' => $request->string('method'), 'reference_no' => $request->string('reference_no'), 'notes' => $request->string('notes'), 'cash_transaction_id' => $cash->id, 'created_by' => auth()->id()]);
            $paid = (float) $invoice->paid_total + $request->float('amount');
            $invoice->update(['paid_total' => $paid, 'status' => $paid >= (float) $invoice->grand_total ? 'paid' : 'partially_paid', 'updated_by' => auth()->id()]);
            $invoice->receivable()->update(['paid_amount' => $paid, 'status' => $paid >= (float) $invoice->grand_total ? 'paid' : 'partially_paid']);
        });
        $audit->record($invoice->fresh(), 'payment', null, ['amount' => $request->float('amount'), 'paid_total' => $invoice->fresh()->paid_total], 'Pembayaran invoice dicatat.');
        return back()->with('success', 'Pembayaran invoice berhasil dicatat.');
    }

    private function terbilang(float $number): string
    {
        $words = ['nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($number < 12) return $words[(int) $number];
        if ($number < 20) return $this->terbilang($number - 10) . ' belas';
        if ($number < 100) return $this->terbilang(floor($number / 10)) . ' puluh ' . $this->terbilang(fmod($number, 10));
        if ($number < 200) return 'seratus ' . $this->terbilang($number - 100);
        if ($number < 1000) return $this->terbilang(floor($number / 100)) . ' ratus ' . $this->terbilang(fmod($number, 100));
        if ($number < 2000) return 'seribu ' . $this->terbilang($number - 1000);
        if ($number < 1000000) return $this->terbilang(floor($number / 1000)) . ' ribu ' . $this->terbilang(fmod($number, 1000));
        if ($number < 1000000000) return $this->terbilang(floor($number / 1000000)) . ' juta ' . $this->terbilang(fmod($number, 1000000));
        return number_format($number, 0, ',', '.') . ' rupiah';
    }
}
