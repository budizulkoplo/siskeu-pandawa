@php
    $isCash = $type === 'cash';
    $isPurchase = $type === 'pembelian';
    $number = $isCash ? $transaction->transaction_no : $transaction->invoice_no;
    $date = $transaction->transaction_date;
    $party = $isPurchase ? $transaction->supplier : ($isCash ? null : $transaction->customer);
    $partyName = $party?->name ?: (!$isPurchase && !$isCash ? ($transaction->customer_name ?: 'Customer Umum') : '-');
    $subtotal = $isCash ? $transaction->amount : $transaction->subtotal;
    $discount = $isCash ? 0 : $transaction->discount;
    $tax = $isCash ? 0 : $transaction->tax;
    $total = $isCash ? $transaction->amount : $transaction->total;
    $title = $isCash ? ($transaction->direction === 'in' ? 'NOTA PENERIMAAN' : 'NOTA PENGELUARAN') : ($isPurchase ? 'NOTA PEMBELIAN' : 'NOTA PENJUALAN');
    $terbilang = function ($value) use (&$terbilang) {
        $words = ['nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $value = floor((float) $value);
        if ($value < 12) return $words[(int) $value];
        if ($value < 20) return $terbilang($value - 10) . ' belas';
        if ($value < 100) return $terbilang(floor($value / 10)) . ' puluh ' . $terbilang($value % 10);
        if ($value < 200) return 'seratus ' . $terbilang($value - 100);
        if ($value < 1000) return $terbilang(floor($value / 100)) . ' ratus ' . $terbilang($value % 100);
        if ($value < 2000) return 'seribu ' . $terbilang($value - 1000);
        if ($value < 1000000) return $terbilang(floor($value / 1000)) . ' ribu ' . $terbilang($value % 1000);
        if ($value < 1000000000) return $terbilang(floor($value / 1000000)) . ' juta ' . $terbilang($value % 1000000);
        return number_format($value, 0, ',', '.') . ' rupiah';
    };
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $number }}</title>
    <style>
        @page { margin: 25px 30px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }
        h1,h2,h3,p { margin: 0; }
        .header { border-bottom: 3px solid #6bb6c9; padding-bottom: 8px; margin-bottom: 12px; min-height: 62px; }
        .logo { height: 55px; max-width: 180px; object-fit: contain; float: left; margin-right: 12px; }
        .company { font-size: 16px; font-weight: bold; color: #555; padding-top: 4px; }
        .muted { color: #555; line-height: 1.4; }
        .right { text-align: right; }
        .grid { width: 100%; display: table; }
        .cell { display: table-cell; width: 50%; vertical-align: top; line-height: 1.5; }
        .info { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th,td { border: 1px solid #555; padding: 5px; }
        th { background: #eaf4f6; }
        .num { text-align: right; }
        .total { font-weight: bold; background: #eaf4f6; }
        .footer { margin-top: 24px; display: table; width: 100%; }
        .footer > div { display: table-cell; width: 50%; vertical-align: top; line-height: 1.5; }
        .signature { text-align: center; }
        .line { border-top: 1px solid #222; width: 180px; margin: 50px auto 0; }
    </style>
</head>
<body>
    <div class="header">
        <img class="logo" src="{{ public_path($setting?->path_logo ?: 'logo.png') }}">
        <div class="company">{{ $setting?->nama_perusahaan ?: 'PT Pandawa' }}</div>
        <div class="muted">{{ $setting?->alamat }}<br>{{ $setting?->telepon }}@if($setting?->npwp) | NPWP: {{ $setting->npwp }}@endif</div>
        <h2 style="text-align:center;margin-top:5px">{{ $title }}</h2>
    </div>
    <div class="grid info">
        <div class="cell"><strong>{{ $isPurchase ? 'Supplier:' : ($isCash ? 'Keterangan:' : 'Customer:') }}</strong><br>{{ $isCash ? $transaction->description : $partyName }}<br>@if($party){{ $party->address }}<br>{{ $party->phone }}@endif</div>
        <div class="cell right"><strong>NO:</strong> {{ $number }}<br><strong>TGL:</strong> {{ $date?->format('d F Y') }}<br>@if(!$isCash)<strong>JATUH TEMPO:</strong> {{ optional($transaction->due_date)->format('d F Y') ?: '-' }}@endif</div>
    </div>
    <table>
        <thead><tr><th>No</th><th>Nama / Deskripsi</th><th>Qty</th><th>No Batch</th><th>Harga Satuan</th><th>Total</th></tr></thead>
        <tbody>
        @if($isCash)
            <tr><td>1</td><td>{{ $transaction->description }}</td><td>1</td><td>-</td><td class="num">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td><td class="num">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td></tr>
        @else
            @foreach($transaction->details as $no => $detail)
                <tr><td>{{ $no + 1 }}</td><td>{{ $detail->item?->name ?: '-' }}</td><td>{{ number_format($detail->quantity, 3, ',', '.') }} {{ $detail->item?->unit }}</td><td>{{ $detail->batch_no ?: '-' }}</td><td class="num">Rp {{ number_format($detail->price, 0, ',', '.') }}</td><td class="num">Rp {{ number_format($detail->total, 0, ',', '.') }}</td></tr>
            @endforeach
        @endif
        <tr><td colspan="5" class="num">Sub Total</td><td class="num">Rp {{ number_format($subtotal, 0, ',', '.') }}</td></tr>
        @if(!$isCash)<tr><td colspan="5" class="num">Diskon</td><td class="num">Rp {{ number_format($discount, 0, ',', '.') }}</td></tr><tr><td colspan="5" class="num">PPN</td><td class="num">Rp {{ number_format($tax, 0, ',', '.') }}</td></tr>@endif
        <tr class="total"><td colspan="5" class="num">TOTAL</td><td class="num">Rp {{ number_format($total, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
    <p style="margin-top:8px"><strong>Terbilang:</strong> {{ ucfirst(trim($terbilang($total))) }} rupiah</p>
    @if(!$isCash && $transaction->notes)<p style="margin-top:8px">{{ $transaction->notes }}</p>@endif
    <div class="footer"><div><strong>Pembayaran melalui transfer ke:</strong><br>{{ $setting?->bank_name }}<br>No. Rekening: {{ $setting?->bank_account }}<br>a.n. {{ $setting?->bank_account_name }}</div><div class="signature">{{ $setting?->nama_perusahaan ?: 'PT Pandawa' }}<div class="line"></div>Penanggung Jawab</div></div>
</body>
</html>
