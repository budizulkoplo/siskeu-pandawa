<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'items' => Item::count(),
            'stock' => InventoryMovement::query()->latest('id')->get()->groupBy('item_id')->sum(fn ($rows) => (float) $rows->last()->balance_after),
            'purchases' => Purchase::whereDate('transaction_date', today())->sum('total'),
            'sales' => Sale::whereDate('transaction_date', today())->sum('total'),
            'cashIn' => CashTransaction::whereDate('transaction_date', today())->where('direction', 'in')->sum('amount'),
            'cashOut' => CashTransaction::whereDate('transaction_date', today())->where('direction', 'out')->sum('amount'),
            'receivables' => AccountReceivable::whereColumn('paid_amount', '<', 'amount')->sum('amount'),
            'overdueReceivables' => AccountReceivable::whereColumn('paid_amount', '<', 'amount')->whereDate('due_date', '<', today())->sum('amount'),
            'payables' => AccountPayable::whereColumn('paid_amount', '<', 'amount')->sum('amount'),
            'overduePayables' => AccountPayable::whereColumn('paid_amount', '<', 'amount')->whereDate('due_date', '<', today())->sum('amount'),
            'movements' => InventoryMovement::with(['item', 'location', 'user'])->latest()->limit(8)->get(),
        ]);
    }
}
