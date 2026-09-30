<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    public function index() { return view('delivery.index', ['orders' => DeliveryOrder::with(['customer', 'invoice'])->latest()->paginate(25)]); }

    public function create(Invoice $invoice) { $invoice->load(['customer', 'items.item']); return view('delivery.form', compact('invoice')); }

    public function store(Request $request, Invoice $invoice, AuditService $audit)
    {
        $request->validate(['delivery_date' => 'required|date', 'recipient_name' => 'required']);
        $order = DB::transaction(function () use ($request, $invoice) {
            $order = DeliveryOrder::create(['delivery_no' => 'SJ-' . now()->format('YmdHis'), 'invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id, 'delivery_date' => $request->date('delivery_date'), 'shipping_address' => $request->string('shipping_address', $invoice->customer->address), 'recipient_name' => $request->string('recipient_name'), 'status' => 'draft', 'notes' => $request->string('notes'), 'created_by' => auth()->id()]);
            foreach ($invoice->items as $item) $order->items()->create(['item_id' => $item->item_id, 'description' => $item->description, 'quantity' => $item->quantity, 'unit' => $item->unit]);
            return $order;
        });
        $audit->record($order, 'created', null, $order->load('items')->toArray(), 'Surat jalan dibuat dari invoice.');
        return redirect()->route('delivery.show', $order)->with('success', 'Surat jalan berhasil dibuat.');
    }

    public function show(DeliveryOrder $deliveryOrder) { return view('delivery.show', ['order' => $deliveryOrder->load(['customer', 'invoice', 'items.item'])]); }
}
