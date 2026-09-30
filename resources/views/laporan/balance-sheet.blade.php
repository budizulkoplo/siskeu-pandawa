@extends('layouts.app')
@section('content')
<h2>Neraca Ringkas</h2>
<div class="card border-0 shadow-sm col-lg-7"><table class="table mb-0"><tbody><tr><th colspan="2" class="table-light">ASET</th></tr><tr><td>Kas Bersih</td><td class="text-end">Rp {{ number_format($cash,0,',','.') }}</td></tr><tr><td>Persediaan (harga beli)</td><td class="text-end">Rp {{ number_format($inventory,0,',','.') }}</td></tr><tr class="table-primary"><th>Total Aset</th><th class="text-end">Rp {{ number_format($cash+$inventory,0,',','.') }}</th></tr></tbody></table></div>
<div class="small text-muted mt-3">Neraca ringkas ini menjadi fondasi laporan Solar; kewajiban, modal, dan jurnal penyesuaian akan ditambahkan bersama modul posting akuntansi.</div>
@endsection

