<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\TransactionLog;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function stock(Request $request)
    {
        $items = Item::with(['category', 'stocks.location'])->orderBy('name')->get();
        return view('laporan.stock', compact('items'));
    }

    public function movements(Request $request)
    {
        $movements = InventoryMovement::with(['item', 'location', 'user'])->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))->latest()->paginate(30);
        return view('laporan.movements', ['movements' => $movements, 'items' => Item::orderBy('name')->get()]);
    }

    public function cashflow(Request $request)
    {
        $rows = CashTransaction::with(['coa', 'erpCode'])->when($request->filled('from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date('from')))->when($request->filled('to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date('to')))->latest('transaction_date')->get();
        return view('laporan.cashflow', compact('rows'));
    }

    public function logs(Request $request)
    {
        $logs = TransactionLog::with('user')->latest()->paginate(40);
        return view('laporan.logs', compact('logs'));
    }

    public function profitLoss(Request $request)
    {
        $from = $request->date('from') ?: now()->startOfMonth();
        $to = $request->date('to') ?: now()->endOfMonth();
        $sales = Sale::whereBetween('transaction_date', [$from, $to])->sum('total');
        $purchases = Purchase::whereBetween('transaction_date', [$from, $to])->sum('total');
        $cashOut = CashTransaction::where('direction', 'out')->whereBetween('transaction_date', [$from, $to])->sum('amount');
        return view('laporan.profit-loss', compact('from', 'to', 'sales', 'purchases', 'cashOut'));
    }

    public function balanceSheet()
    {
        $inventory = Item::with(['stocks', 'category'])->get()->sum(fn ($item) => $item->stocks->sum('quantity') * (float) $item->purchase_price);
        $cashIn = CashTransaction::where('direction', 'in')->sum('amount');
        $cashOut = CashTransaction::where('direction', 'out')->sum('amount');
        return view('laporan.balance-sheet', ['inventory' => $inventory, 'cash' => $cashIn - $cashOut]);
    }

    public function receivables()
    {
        $rows = AccountReceivable::with(['invoice', 'customer'])->whereColumn('paid_amount', '<', 'amount')->latest()->paginate(30);
        return view('laporan.receivables', compact('rows'));
    }

    public function payables()
    {
        $rows = AccountPayable::with(['purchase', 'supplier'])->whereColumn('paid_amount', '<', 'amount')->latest()->paginate(30);
        return view('laporan.payables', compact('rows'));
    }
}
