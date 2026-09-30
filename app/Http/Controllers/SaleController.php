<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Models\Sale;
use App\Services\AuditService;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index() { return view('transaksi.index', ['type' => 'penjualan', 'rows' => Sale::with(['location', 'details.item'])->latest()->get()]); }
    public function create() { return view('transaksi.form', ['type' => 'penjualan', 'items' => Item::where('is_active', true)->orderBy('name')->get(), 'locations' => Location::where('is_active', true)->orderBy('name')->get(), 'suppliers' => collect(), 'customers' => \App\Models\Customer::orderBy('name')->get()]); }

    public function store(Request $request, InventoryService $inventory, AuditService $audit)
    {
        $request->validate([
            'invoice_no' => ['nullable', 'string', 'max:50', Rule::unique('sales', 'invoice_no')],
            'transaction_date' => 'required|date',
            'location_id' => 'required|exists:locations,id',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.batch_no' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:.001',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:nominal,persen',
            'discount_value' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,credit',
        ]);
        $sale = DB::transaction(function () use ($request, $inventory) {
            $lines = collect($request->input('items'))->map(fn ($line) => [...$line, 'total' => (float) $line['quantity'] * (float) $line['price']]);
            $subtotal = $lines->sum('total');
            $discountValue = $request->float('discount_value');
            $discount = $request->input('discount_type', 'nominal') === 'persen'
                ? $subtotal * $discountValue / 100
                : ($request->has('discount_value') ? $discountValue : $request->float('discount'));
            $tax = $request->float('tax');
            $total = max(0, $subtotal - $discount + $tax);
            $customer = $request->integer('customer_id') ? \App\Models\Customer::find($request->integer('customer_id')) : null;
            $sale = Sale::create(['invoice_no' => $request->filled('invoice_no') ? $request->string('invoice_no')->toString() : 'PJ-' . now()->format('YmdHis'), 'transaction_date' => $request->date('transaction_date'), 'customer_name' => $customer?->name ?: $request->string('customer_name')->toString(), 'customer_id' => $customer?->id, 'location_id' => $request->integer('location_id'), 'due_date' => $request->date('due_date'), 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $total, 'payment_method' => $request->string('payment_method', 'cash')->toString(), 'payment_term' => $request->string('payment_term')->toString() ?: null, 'status' => $request->string('payment_method')->toString() === 'credit' ? 'unpaid' : 'completed', 'notes' => $request->string('notes')->toString() ?: null, 'created_by' => auth()->id()]);
            foreach ($lines as $line) {
                $sale->details()->create($line);
                $inventory->move(['item_id' => $line['item_id'], 'location_id' => $sale->location_id, 'movement_type' => 'penjualan', 'direction' => 'out', 'quantity' => $line['quantity'], 'unit_price' => $line['price'], 'reference_type' => Sale::class, 'reference_id' => $sale->id, 'reference_no' => $sale->invoice_no, 'notes' => 'Stok keluar dari penjualan']);
            }
            return $sale;
        });
        $audit->record($sale, 'created', null, $sale->load('details')->toArray(), 'Penjualan dibuat dan stok dikurangi.');
        return redirect()->route('sales.index')->with('success', "Penjualan {$sale->invoice_no} berhasil disimpan.")->with('print_url', route('sales.print', $sale));
    }

    public function print(Sale $sale)
    {
        $sale->load(['customer', 'location', 'details.item']);

        return Pdf::loadView('transaksi.print', [
            'type' => 'penjualan',
            'transaction' => $sale,
            'setting' => \App\Models\Setting::first(),
        ])->setPaper('a4')->stream('nota-' . $sale->invoice_no . '.pdf');
    }

    public function logs(Sale $sale) { return view('transaksi.logs', ['title' => 'Log Penjualan ' . $sale->invoice_no, 'logs' => \App\Models\TransactionLog::with('user')->where('auditable_type', Sale::class)->where('auditable_id', $sale->id)->latest()->get()]); }
}
