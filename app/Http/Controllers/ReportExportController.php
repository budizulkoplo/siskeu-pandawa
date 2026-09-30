<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\CashTransaction;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\TransactionLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportExportController extends Controller
{
    public function export(Request $request, string $report, string $format)
    {
        abort_unless(in_array($report, $this->reports(), true), 404);
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        $dataset = $this->dataset($report, $request);
        $filename = 'laporan-' . $report . '-' . now()->format('YmdHis');

        if ($format === 'pdf') {
            return Pdf::loadView('laporan.export-pdf', [
                'title' => $dataset['title'],
                'headers' => $dataset['headers'],
                'rows' => $dataset['rows'],
            ])->setPaper('a4', 'landscape')->download($filename . '.pdf');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($dataset['title'], 0, 31));
        $sheet->fromArray([$dataset['headers'], ...$dataset['rows']], null, 'A1');
        $sheet->getStyle('A1:' . $this->columnName(count($dataset['headers'])) . '1')->getFont()->setBold(true);
        foreach (range(1, count($dataset['headers'])) as $column) {
            $sheet->getColumnDimension($this->columnName($column))->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename . '.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function reports(): array
    {
        return ['stock', 'movements', 'cashflow', 'logs', 'profit-loss', 'balance-sheet', 'receivables', 'payables'];
    }

    private function dataset(string $report, Request $request): array
    {
        return match ($report) {
            'stock' => $this->stock(),
            'movements' => $this->movements($request),
            'cashflow' => $this->cashflow($request),
            'logs' => $this->logs(),
            'profit-loss' => $this->profitLoss($request),
            'balance-sheet' => $this->balanceSheet(),
            'receivables' => $this->receivables(),
            'payables' => $this->payables(),
        };
    }

    private function stock(): array
    {
        $rows = [];
        foreach (Item::with(['category', 'stocks.location'])->orderBy('name')->get() as $item) {
            if ($item->stocks->isEmpty()) {
                $rows[] = [$item->code, $item->name, $item->category->name ?? '-', '-', 0, $item->unit];
                continue;
            }
            foreach ($item->stocks as $stock) {
                $rows[] = [$item->code, $item->name, $item->category->name ?? '-', $stock->location->name ?? '-', (float) $stock->quantity, $item->unit];
            }
        }
        return ['title' => 'Laporan Stok', 'headers' => ['Kode', 'Barang', 'Kategori', 'Lokasi', 'Stok', 'Satuan'], 'rows' => $rows];
    }

    private function movements(Request $request): array
    {
        $rows = InventoryMovement::with(['item', 'location', 'user'])->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))->latest()->get()->map(fn ($row) => [$row->created_at->format('d/m/Y H:i'), $row->item->name ?? '-', $row->location->name ?? '-', $row->reference_no, $row->direction === 'in' ? 'Masuk' : 'Keluar', (float) $row->quantity, (float) $row->balance_after, $row->user->name ?? '-'])->all();
        return ['title' => 'Kartu Stok', 'headers' => ['Waktu', 'Barang', 'Lokasi', 'Referensi', 'Arah', 'Qty', 'Saldo', 'User'], 'rows' => $rows];
    }

    private function cashflow(Request $request): array
    {
        $rows = CashTransaction::with(['coa'])->when($request->filled('from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date('from')))->when($request->filled('to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date('to')))->latest('transaction_date')->get()->map(fn ($row) => [$row->transaction_no, $row->transaction_date->format('d/m/Y'), $row->direction === 'in' ? 'Penerimaan' : 'Pengeluaran', $row->description, $row->coa->code ?? '-', (float) $row->amount])->all();
        return ['title' => 'Arus Kas', 'headers' => ['No', 'Tanggal', 'Arah', 'Keterangan', 'COA', 'Nominal'], 'rows' => $rows];
    }

    private function logs(): array
    {
        $rows = TransactionLog::with('user')->latest()->get()->map(fn ($log) => [$log->created_at->format('d/m/Y H:i:s'), class_basename($log->auditable_type), $log->auditable_id, strtoupper($log->action), $log->user->name ?? 'System', $log->ip_address, $log->description])->all();
        return ['title' => 'Log Perubahan', 'headers' => ['Waktu', 'Modul', 'ID', 'Aksi', 'User', 'IP', 'Uraian'], 'rows' => $rows];
    }

    private function profitLoss(Request $request): array
    {
        $from = $request->date('from') ?: now()->startOfMonth();
        $to = $request->date('to') ?: now()->endOfMonth();
        $sales = Sale::whereBetween('transaction_date', [$from, $to])->sum('total');
        $purchases = Purchase::whereBetween('transaction_date', [$from, $to])->sum('total');
        $cashOut = CashTransaction::where('direction', 'out')->whereBetween('transaction_date', [$from, $to])->sum('amount');
        return ['title' => 'Laporan Laba Rugi', 'headers' => ['Keterangan', 'Nominal'], 'rows' => [['Pendapatan Penjualan', (float) $sales], ['Harga Pembelian', (float) $purchases], ['Pengeluaran Kas', (float) $cashOut], ['Laba/Rugi Sementara', (float) ($sales - $purchases - $cashOut)]]];
    }

    private function balanceSheet(): array
    {
        $inventory = Item::with('stocks')->get()->sum(fn ($item) => $item->stocks->sum('quantity') * (float) $item->purchase_price);
        $cashIn = CashTransaction::where('direction', 'in')->sum('amount');
        $cashOut = CashTransaction::where('direction', 'out')->sum('amount');
        $cash = $cashIn - $cashOut;
        return ['title' => 'Neraca Ringkas', 'headers' => ['Keterangan', 'Nominal'], 'rows' => [['Kas Bersih', (float) $cash], ['Persediaan (harga beli)', (float) $inventory], ['Total Aset', (float) ($cash + $inventory)]]];
    }

    private function receivables(): array
    {
        $rows = AccountReceivable::with(['invoice', 'customer'])->whereColumn('paid_amount', '<', 'amount')->latest()->get()->map(fn ($row) => [$row->invoice->invoice_no ?? '-', $row->customer->name ?? '-', $row->due_date?->format('d/m/Y') ?? '-', (float) $row->amount, (float) $row->paid_amount, (float) ($row->amount - $row->paid_amount), $row->due_date && $row->due_date->isPast() ? 'Jatuh Tempo' : 'Belum Jatuh Tempo'])->all();
        return ['title' => 'Laporan Piutang', 'headers' => ['Invoice', 'Pelanggan', 'Jatuh Tempo', 'Nilai', 'Terbayar', 'Sisa', 'Status'], 'rows' => $rows];
    }

    private function payables(): array
    {
        $rows = AccountPayable::with(['purchase', 'supplier'])->whereColumn('paid_amount', '<', 'amount')->latest()->get()->map(fn ($row) => [$row->purchase->invoice_no ?? '-', $row->supplier->name ?? '-', $row->due_date?->format('d/m/Y') ?? '-', (float) $row->amount, (float) $row->paid_amount, (float) ($row->amount - $row->paid_amount), $row->due_date && $row->due_date->isPast() ? 'Jatuh Tempo' : 'Belum Jatuh Tempo'])->all();
        return ['title' => 'Laporan Hutang', 'headers' => ['Pembelian', 'Pemasok', 'Jatuh Tempo', 'Nilai', 'Terbayar', 'Sisa', 'Status'], 'rows' => $rows];
    }

    private function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $name = chr(65 + $remainder) . $name;
            $column = intdiv($column - 1, 26);
        }
        return $name;
    }
}
