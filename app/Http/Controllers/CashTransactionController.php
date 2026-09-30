<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Coa;
use App\Models\ErpTransactionCode;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CashTransactionController extends Controller
{
    public function index(string $direction)
    {
        abort_unless(in_array($direction, ['in', 'out'], true), 404);
        return view('transaksi.cash', ['direction' => $direction, 'rows' => CashTransaction::with(['coa', 'erpCode'])->where('direction', $direction)->latest()->get(), 'coas' => Coa::orderBy('code')->get(), 'codes' => ErpTransactionCode::where('direction', $direction)->where('is_active', true)->orderBy('code')->get()]);
    }

    public function store(Request $request, string $direction, AuditService $audit)
    {
        abort_unless(in_array($direction, ['in', 'out'], true), 404);
        $request->validate(['transaction_date' => 'required|date', 'amount' => 'required|numeric|min:.01', 'description' => 'required']);
        $transaction = CashTransaction::create(['transaction_no' => strtoupper($direction === 'in' ? 'RCV-' : 'PAY-') . now()->format('YmdHis'), 'transaction_date' => $request->date('transaction_date'), 'direction' => $direction, 'coa_id' => $request->integer('coa_id') ?: null, 'erp_code_id' => $request->integer('erp_code_id') ?: null, 'amount' => $request->float('amount'), 'description' => $request->string('description'), 'created_by' => auth()->id()]);
        $audit->record($transaction, 'created', null, $transaction->toArray(), strtoupper($direction === 'in' ? 'Penerimaan' : 'Pengeluaran') . ' dicatat.');
        $printRoute = $direction === 'in' ? 'cash.in.print' : 'cash.out.print';
        return back()->with('success', 'Transaksi kas berhasil disimpan.')
            ->with('print_url', route($printRoute, $transaction));
    }

    public function print(CashTransaction $transaction, string $direction)
    {
        abort_unless($transaction->direction === $direction, 404);
        $transaction->load(['coa', 'erpCode']);

        return Pdf::loadView('transaksi.print', [
            'type' => 'cash',
            'transaction' => $transaction,
            'setting' => \App\Models\Setting::first(),
        ])->setPaper('a4')->stream('nota-' . $transaction->transaction_no . '.pdf');
    }
}
