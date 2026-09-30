@extends('layouts.app')
@section('content')
<h2>Laporan Laba Rugi</h2>
<form class="row g-2 mb-3"><div class="col-md-3"><input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="form-control"></div><div class="col-md-3"><input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="form-control"></div><div class="col-auto"><button class="btn btn-primary">Tampilkan</button></div></form>
<div class="card border-0 shadow-sm col-lg-7"><table class="table mb-0"><tbody><tr><th>Pendapatan Penjualan</th><td class="text-end text-success">Rp {{ number_format($sales,0,',','.') }}</td></tr><tr><th>Harga Pembelian</th><td class="text-end">Rp {{ number_format($purchases,0,',','.') }}</td></tr><tr><th>Pengeluaran Kas</th><td class="text-end">Rp {{ number_format($cashOut,0,',','.') }}</td></tr><tr class="table-primary"><th>Laba/Rugi Sementara</th><th class="text-end">Rp {{ number_format($sales-$purchases-$cashOut,0,',','.') }}</th></tr></tbody></table></div>
<div class="small text-muted mt-3">Perhitungan awal menggunakan total transaksi. Jurnal akuntansi detail akan diperluas saat modul posting otomatis COA diaktifkan.</div>
@endsection

