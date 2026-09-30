@extends('layouts.app')

@section('content')
@php($purchase = $type === 'pembelian')
@php($itemOptions = $items->map(fn ($item) => ['id' => $item->id, 'label' => $item->code . ' - ' . $item->name . ' (' . $item->unit . ')', 'buy' => $item->purchase_price, 'sale' => $item->sale_price])->values())
<style>
    .transaction-form .card-header { display: flex; align-items: center; min-height: 48px; }
    .transaction-form .detail-toolbar { margin-left: auto; }
    .transaction-form .form-label { margin-bottom: .35rem; font-weight: 500; }
    .transaction-form #detailTable th { white-space: nowrap; vertical-align: middle; }
    .transaction-form #detailTable tfoot td { vertical-align: middle; }
    .transaction-form .summary-label { text-align: right; white-space: nowrap; font-weight: 500; }
    .transaction-form .summary-control { width: 100%; max-width: 230px; margin-left: auto; }
    .transaction-form .form-actions { display: flex; justify-content: flex-end; gap: .5rem; }
</style>
<div class="app-content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h3 class="mb-0">Form Nota {{ $purchase ? 'Pembelian' : 'Penjualan' }}</h3><small class="text-muted">Format transaksi mengikuti pola Solar dengan detail Pandawa.</small></div>
        <a class="btn btn-sm btn-light" href="{{ route($purchase ? 'purchases.index' : 'sales.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

<form method="POST" action="{{ route($purchase ? 'purchases.store' : 'sales.store') }}" id="transactionForm" class="transaction-form">
    @csrf
    <div class="card card-info card-outline mb-3">
        <div class="card-header"><strong>Informasi Nota</strong></div>
        <div class="card-body"><div class="row g-2">
            <div class="col-md-4"><label class="form-label">No Invoice</label><input name="invoice_no" class="form-control form-control-sm" placeholder="Kosongkan untuk nomor otomatis"></div>
            <div class="col-md-4"><label class="form-label">Tanggal *</label><input type="date" name="transaction_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div>
            <div class="col-md-4"><label class="form-label">Lokasi *</label><select name="location_id" class="form-select form-select-sm" required><option value="">-- Pilih Lokasi --</option>@foreach($locations as $row)<option value="{{ $row->id }}">{{ $row->code }} - {{ $row->name }}</option>@endforeach</select></div>
            @if($purchase)
                <div class="col-md-4"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select form-select-sm"><option value="">-- Pilih Supplier --</option>@foreach($suppliers as $row)<option value="{{ $row->id }}">{{ $row->code }} - {{ $row->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">No Invoice Supplier</label><input name="supplier_invoice_no" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="form-label">Status Pembayaran</label><select name="payment_status" class="form-select form-select-sm"><option value="unpaid">Belum Bayar / Kredit</option><option value="paid">Lunas</option></select></div>
            @else
                <div class="col-md-4"><label class="form-label">Customer</label><select name="customer_id" class="form-select form-select-sm"><option value="">-- Pilih Customer --</option>@foreach($customers as $row)<option value="{{ $row->id }}">{{ $row->code }} - {{ $row->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Customer Umum</label><input name="customer_name" class="form-control form-control-sm" placeholder="Nama customer bila belum ada master"></div>
                <div class="col-md-4"><label class="form-label">Metode Pembayaran</label><select name="payment_method" class="form-select form-select-sm"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="credit">Tempo / Kredit</option></select></div>
            @endif
            <div class="col-md-4"><label class="form-label">Tanggal Tempo</label><input type="date" name="due_date" class="form-control form-control-sm"></div>
            <div class="col-md-4"><label class="form-label">Termin Pembayaran</label><input name="payment_term" class="form-control form-control-sm" placeholder="Contoh: 30 hari"></div>
        </div></div>
    </div>

    <div class="card card-info card-outline">
        <div class="card-header py-2"><strong>Detail Barang</strong><div class="detail-toolbar"><button type="button" class="btn btn-sm btn-success" id="addRow"><i class="bi bi-plus"></i> Tambah Baris</button></div></div>
        <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0" id="detailTable" style="font-size:small"><thead><tr><th width="45">No</th><th>Nama Barang</th><th width="100">Qty</th><th width="150">No Batch</th><th width="160">Harga Satuan</th><th width="160">Total</th><th width="45"></th></tr></thead><tbody>
            <tr><td class="text-center row-number">1</td><td><select name="items[0][item_id]" class="form-select form-select-sm item-select" required><option value="">-- Pilih Barang --</option>@foreach($items as $item)<option value="{{ $item->id }}" data-buy="{{ $item->purchase_price }}" data-sale="{{ $item->sale_price }}">{{ $item->code }} - {{ $item->name }} ({{ $item->unit }})</option>@endforeach</select></td><td><input name="items[0][quantity]" type="number" step=".001" min=".001" value="1" class="form-control form-control-sm text-end quantity" required></td><td><input name="items[0][batch_no]" class="form-control form-control-sm"></td><td><input name="items[0][price]" type="number" step=".01" min="0" value="0" class="form-control form-control-sm text-end price" required></td><td><input type="text" class="form-control form-control-sm text-end line-total" value="0" readonly></td><td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td></tr>
        </tbody><tfoot>
            <tr><td colspan="5" class="summary-label">Subtotal</td><td><input id="subtotalDisplay" class="form-control form-control-sm text-end summary-control" value="0" readonly></td><td></td></tr>
            <tr><td colspan="5" class="summary-label">Diskon</td><td><div class="input-group input-group-sm summary-control"><select name="discount_type" id="discountType" class="form-select"><option value="nominal">Nominal</option><option value="persen">%</option></select><input name="discount_value" id="discountValue" type="number" step=".01" min="0" value="0" class="form-control text-end"></div><input type="hidden" name="discount" id="discount" value="0"></td><td></td></tr>
            <tr><td colspan="5" class="summary-label">PPN / Pajak</td><td><input name="tax" id="tax" type="number" step=".01" min="0" value="0" class="form-control form-control-sm text-end summary-control"></td><td></td></tr>
            <tr><td colspan="5" class="summary-label fw-bold">Grand Total</td><td><input id="grandTotalDisplay" class="form-control form-control-sm text-end fw-bold summary-control" value="0" readonly></td><td></td></tr>
        </tfoot></table></div>
        <div class="card-body"><label class="form-label">Catatan</label><textarea name="notes" class="form-control mb-3" rows="2"></textarea><div class="form-actions"><a href="{{ route($purchase ? 'purchases.index' : 'sales.index') }}" class="btn btn-light">Batal</a><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Transaksi</button></div></div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    let rowIndex = 1;
    const body = document.querySelector('#detailTable tbody');
    const priceKey = @json($purchase ? 'buy' : 'sale');
    const items = @json($itemOptions);
    const money = value => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const options = () => '<option value="">-- Pilih Barang --</option>' + items.map(item => `<option value="${item.id}" data-buy="${item.buy}" data-sale="${item.sale}">${item.label}</option>`).join('');
    function calculate() {
        let subtotal = 0;
        body.querySelectorAll('tr').forEach((row, index) => { row.querySelector('.row-number').textContent = index + 1; const total = (Number(row.querySelector('.quantity').value) || 0) * (Number(row.querySelector('.price').value) || 0); row.querySelector('.line-total').value = money(total); subtotal += total; });
        const discountValue = Number(document.querySelector('#discountValue').value) || 0;
        const discount = document.querySelector('#discountType').value === 'persen' ? subtotal * discountValue / 100 : discountValue;
        const tax = Number(document.querySelector('#tax').value) || 0;
        document.querySelector('#discount').value = discount;
        document.querySelector('#subtotalDisplay').value = money(subtotal);
        document.querySelector('#grandTotalDisplay').value = money(Math.max(0, subtotal - discount + tax));
    }
    function bind(row) {
        row.querySelector('.item-select').addEventListener('change', event => { row.querySelector('.price').value = event.target.selectedOptions[0]?.dataset[priceKey] || 0; calculate(); });
        row.querySelectorAll('input').forEach(input => input.addEventListener('input', calculate));
        row.querySelector('.remove-row').addEventListener('click', () => { if (body.children.length > 1) { row.remove(); calculate(); } });
    }
    bind(body.querySelector('tr'));
    document.querySelector('#addRow').addEventListener('click', () => { const row = document.createElement('tr'); row.innerHTML = `<td class="text-center row-number"></td><td><select name="items[${rowIndex}][item_id]" class="form-select form-select-sm item-select" required>${options()}</select></td><td><input name="items[${rowIndex}][quantity]" type="number" step=".001" min=".001" value="1" class="form-control form-control-sm text-end quantity" required></td><td><input name="items[${rowIndex}][batch_no]" class="form-control form-control-sm"></td><td><input name="items[${rowIndex}][price]" type="number" step=".01" min="0" value="0" class="form-control form-control-sm text-end price" required></td><td><input type="text" class="form-control form-control-sm text-end line-total" value="0" readonly></td><td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>`; body.appendChild(row); bind(row); rowIndex++; });
    document.querySelectorAll('#discountType, #discountValue, #tax').forEach(input => input.addEventListener('input', calculate));
    calculate();
})();
</script>
@endpush
