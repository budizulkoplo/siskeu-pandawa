<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coa;
use App\Models\Customer;
use App\Models\ErpTransactionCode;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['username' => 'admin'], ['name' => 'Administrator Pandawa', 'email' => 'admin@pandawa.local', 'password' => 'password', 'status' => 'aktif']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $admin->assignRole($superadmin);
        foreach ([['admin', 'Administrator'], ['keuangan', 'Keuangan'], ['gudang', 'Gudang']] as [$name]) Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        Setting::firstOrCreate([], ['nama_perusahaan' => 'PT Pandawa']);
        foreach ([['1000', 'Kas', 'asset'], ['1100', 'Bank', 'asset'], ['1200', 'Persediaan', 'asset'], ['4000', 'Pendapatan Penjualan', 'income'], ['5000', 'Harga Pokok Penjualan', 'expense']] as [$code, $name, $type]) Coa::firstOrCreate(['code' => $code], ['name' => $name, 'type' => $type]);
        foreach ([['RCV', 'Penerimaan Umum', 'in'], ['PAY', 'Pengeluaran Umum', 'out']] as [$code, $name, $direction]) ErpTransactionCode::firstOrCreate(['code' => $code], ['name' => $name, 'direction' => $direction]);
        Category::firstOrCreate(['code' => 'UMUM'], ['name' => 'Umum']);
        Location::firstOrCreate(['code' => 'UTAMA'], ['name' => 'Gudang Utama']);
        Customer::firstOrCreate(['code' => 'UMUM'], ['name' => 'Customer Umum', 'status' => 'aktif']);
        $roles = 'superadmin;admin;keuangan;gudang';
        $ensureMenu = function (string $name, ?string $link, string $icon, int $seq, ?int $parentId = null) use ($roles): Menu {
            return Menu::updateOrCreate(
                ['name' => $name],
                ['link' => $link, 'icon' => $icon, 'role' => $roles, 'seq' => $seq, 'parent_id' => $parentId]
            );
        };

        $ensureMenu('Dashboard', 'dashboard', 'bi bi-speedometer2', 1);
        $master = $ensureMenu('MASTER', null, 'bi bi-folder2-open', 10);
        foreach ([
            ['User', 'master.users', 'bi bi-people'], ['COA', 'master.coa', 'bi bi-journal-text'],
            ['Kategori', 'master.kategori', 'bi bi-tags'], ['Lokasi', 'master.lokasi', 'bi bi-geo-alt'],
            ['Barang', 'master.barang', 'bi bi-boxes'], ['Supplier', 'master.supplier', 'bi bi-truck'],
            ['Customer', 'master.customer', 'bi bi-people-fill'],
        ] as $index => [$name, $link, $icon]) $ensureMenu($name, $link, $icon, 11 + $index, $master->id);

        $transactions = $ensureMenu('TRANSAKSI', null, 'bi bi-arrow-left-right', 20);
        foreach ([
            ['Pembelian', 'purchases.index', 'bi bi-cart-plus'], ['Penjualan', 'sales.index', 'bi bi-cart-check'],
            ['Penerimaan', 'cash.in', 'bi bi-box-arrow-in-down'], ['Pengeluaran', 'cash.out', 'bi bi-box-arrow-up'],
            ['Invoice', 'invoices.index', 'bi bi-receipt'], ['Surat Jalan', 'delivery.index', 'bi bi-truck'],
        ] as $index => [$name, $link, $icon]) $ensureMenu($name, $link, $icon, 21 + $index, $transactions->id);

        $reports = $ensureMenu('LAPORAN', null, 'bi bi-bar-chart', 30);
        foreach ([
            ['Stok', 'reports.stock', 'bi bi-boxes'], ['Kartu Stok', 'reports.movements', 'bi bi-arrow-left-right'],
            ['Arus Kas', 'reports.cashflow', 'bi bi-journal-text'], ['Log Perubahan', 'reports.logs', 'bi bi-clock-history'],
            ['Laba Rugi', 'reports.profit-loss', 'bi bi-graph-up'], ['Neraca', 'reports.balance-sheet', 'bi bi-bank'],
            ['Piutang', 'reports.receivables', 'bi bi-person-lines-fill'], ['Hutang', 'reports.payables', 'bi bi-receipt-cutoff'],
        ] as $index => [$name, $link, $icon]) $ensureMenu($name, $link, $icon, 31 + $index, $reports->id);

        $settings = $ensureMenu('SETTING', null, 'bi bi-gear', 40);
        foreach ([
            ['Role', 'settings.roles', 'bi bi-shield-lock'], ['Menu', 'settings.menus', 'bi bi-list-nested'],
            ['ERP Master', 'master.erp', 'bi bi-gear'],
        ] as $index => [$name, $link, $icon]) $ensureMenu($name, $link, $icon, 41 + $index, $settings->id);
    }
}
