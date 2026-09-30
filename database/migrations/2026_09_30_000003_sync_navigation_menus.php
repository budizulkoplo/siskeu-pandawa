<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $roles = 'superadmin;admin;keuangan;gudang';

        // Rename the two legacy seed records before creating the canonical menu tree.
        foreach ([['Master Barang', 'Barang'], ['Laporan Stok', 'Stok']] as [$old, $new]) {
            if (!Menu::where('name', $new)->exists()) {
                Menu::where('name', $old)->update(['name' => $new]);
            }
        }

        $ensure = function (string $name, ?string $link, string $icon, int $seq, ?int $parentId = null) use ($roles): Menu {
            $menu = Menu::where('name', $name)->first();
            if (!$menu) {
                $menu = new Menu(['name' => $name, 'role' => $roles]);
            }

            $menu->link = $link;
            $menu->icon = $icon;
            $menu->seq = $seq;
            $menu->parent_id = $parentId;
            if (!$menu->role) $menu->role = $roles;
            $menu->save();

            return $menu;
        };

        $ensure('Dashboard', 'dashboard', 'bi bi-speedometer2', 1);

        $master = $ensure('MASTER', null, 'bi bi-folder2-open', 10);
        foreach ([
            ['User', 'master.users', 'bi bi-people'],
            ['COA', 'master.coa', 'bi bi-journal-text'],
            ['Kategori', 'master.kategori', 'bi bi-tags'],
            ['Lokasi', 'master.lokasi', 'bi bi-geo-alt'],
            ['Barang', 'master.barang', 'bi bi-boxes'],
            ['Supplier', 'master.supplier', 'bi bi-truck'],
            ['Customer', 'master.customer', 'bi bi-people-fill'],
        ] as $index => [$name, $link, $icon]) {
            $ensure($name, $link, $icon, 11 + $index, $master->id);
        }

        $transactions = $ensure('TRANSAKSI', null, 'bi bi-arrow-left-right', 20);
        foreach ([
            ['Pembelian', 'purchases.index', 'bi bi-cart-plus'],
            ['Penjualan', 'sales.index', 'bi bi-cart-check'],
            ['Penerimaan', 'cash.in', 'bi bi-box-arrow-in-down'],
            ['Pengeluaran', 'cash.out', 'bi bi-box-arrow-up'],
            ['Invoice', 'invoices.index', 'bi bi-receipt'],
            ['Surat Jalan', 'delivery.index', 'bi bi-truck'],
        ] as $index => [$name, $link, $icon]) {
            $ensure($name, $link, $icon, 21 + $index, $transactions->id);
        }

        $reports = $ensure('LAPORAN', null, 'bi bi-bar-chart', 30);
        foreach ([
            ['Stok', 'reports.stock', 'bi bi-boxes'],
            ['Kartu Stok', 'reports.movements', 'bi bi-arrow-left-right'],
            ['Arus Kas', 'reports.cashflow', 'bi bi-journal-text'],
            ['Log Perubahan', 'reports.logs', 'bi bi-clock-history'],
            ['Laba Rugi', 'reports.profit-loss', 'bi bi-graph-up'],
            ['Neraca', 'reports.balance-sheet', 'bi bi-bank'],
            ['Piutang', 'reports.receivables', 'bi bi-person-lines-fill'],
            ['Hutang', 'reports.payables', 'bi bi-receipt-cutoff'],
        ] as $index => [$name, $link, $icon]) {
            $ensure($name, $link, $icon, 31 + $index, $reports->id);
        }

        $settings = $ensure('SETTING', null, 'bi bi-gear', 40);
        foreach ([
            ['Role', 'settings.roles', 'bi bi-shield-lock'],
            ['Menu', 'settings.menus', 'bi bi-list-nested'],
            ['ERP Master', 'master.erp', 'bi bi-gear'],
        ] as $index => [$name, $link, $icon]) {
            $ensure($name, $link, $icon, 41 + $index, $settings->id);
        }
    }

    public function down(): void
    {
        // Navigation data is application configuration and is intentionally retained on rollback.
    }
};
