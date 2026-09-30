<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\AccountPayable;
use App\Models\CashTransaction;
use App\Models\Location;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\AuditService;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function index() { return view('transaksi.index', ['type' => 'pembelian', 'rows' => Purchase::with(['supplier', 'location', 'details.item'])->latest()->get()]); }
    public function create() { return view('transaksi.form', ['type' => 'pembelian', 'items' => Item::where('is_active', true)->orderBy('name')->get(), 'locations' => Location::where('is_active', true)->orderBy('name')->get(), 'suppliers' => Supplier::orderBy('name')->get(), 'customers' => collect()]); }

    public function store(Request $request, InventoryService $inventory, AuditService $audit)
    {
        $request->validate([
            'invoice_no' => ['nullable', 'string', 'max:50', Rule::unique('purchases', 'invoice_no')],
            'transaction_date' => 'required|date',
            'location_id' => 'required|exists:locations,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.batch_no' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:.001',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:nominal,persen',
            'discount_value' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:unpaid,paid',
        ]);
        $purchase = DB::transaction(function () use ($request, $inventory) {
            $lines = collect($request->input('items'))->map(fn ($line) => [...$line, 'total' => (float) $line['quantity'] * (float) $line['price']]);
            $subtotal = $lines->sum('total');
            $discountValue = $request->float('discount_value');
            $discount = $request->input('discount_type', 'nominal') === 'persen'
                ? $subtotal * $discountValue / 100
                : ($request->has('discount_value') ? $discountValue : $request->float('discount'));
            $tax = $request->float('tax');
            $total = max(0, $subtotal - $discount + $tax);
            $paymentStatus = $request->string('payment_status', 'unpaid')->toString();
            $purchase = Purchase::create(['invoice_no' => $request->filled('invoice_no') ? $request->string('invoice_no')->toString() : 'PB-' . now()->format('YmdHis'), 'transaction_date' => $request->date('transaction_date'), 'supplier_id' => $request->integer('supplier_id') ?: null, 'location_id' => $request->integer('location_id'), 'supplier_invoice_no' => $request->string('supplier_invoice_no')->toString() ?: null, 'due_date' => $request->date('due_date'), 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $total, 'payment_term' => $request->string('payment_term')->toString() ?: null, 'payment_status' => $paymentStatus, 'notes' => $request->string('notes')->toString() ?: null, 'created_by' => auth()->id()]);
            foreach ($lines as $line) {
                $detail = $purchase->details()->create($line);
                $inventory->move(['item_id' => $line['item_id'], 'location_id' => $purchase->location_id, 'movement_type' => 'pembelian', 'direction' => 'in', 'quantity' => $line['quantity'], 'unit_price' => $line['price'], 'reference_type' => Purchase::class, 'reference_id' => $purchase->id, 'reference_no' => $purchase->invoice_no, 'notes' => 'Stok masuk dari pembelian']);
            }
            $paid = $paymentStatus === 'paid' ? $total : 0;
            AccountPayable::create(['purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id, 'amount' => $total, 'paid_amount' => $paid, 'due_date' => $purchase->due_date, 'status' => $paid >= $total ? 'paid' : 'unpaid']);
            if ($paid > 0) CashTransaction::create(['transaction_no' => 'PAY-' . now()->format('YmdHis'), 'transaction_date' => $purchase->transaction_date, 'direction' => 'out', 'amount' => $paid, 'description' => 'Pembayaran pembelian ' . $purchase->invoice_no, 'reference_type' => Purchase::class, 'reference_id' => $purchase->id, 'created_by' => auth()->id()]);
            return $purchase;
        });
        $audit->record($purchase, 'created', null, $purchase->load('details')->toArray(), 'Pembelian dibuat dan stok ditambahkan.');
        return redirect()->route('purchases.index')->with('success', "Pembelian {$purchase->invoice_no} berhasil disimpan.")->with('print_url', route('purchases.print', $purchase));
    }

    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'location', 'details.item']);

        return Pdf::loadView('transaksi.print', [
            'type' => 'pembelian',
            'transaction' => $purchase,
            'setting' => \App\Models\Setting::first(),
        ])->setPaper('a4')->stream('nota-' . $purchase->invoice_no . '.pdf');
    }

    public function logs(Purchase $purchase) { return view('transaksi.logs', ['title' => 'Log Pembelian ' . $purchase->invoice_no, 'logs' => \App\Models\TransactionLog::with('user')->where('auditable_type', Purchase::class)->where('auditable_id', $purchase->id)->latest()->get()]); }
}
