<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['purchase_details', 'sale_details'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'batch_no')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('batch_no', 100)->nullable()->after('item_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['purchase_details', 'sale_details'] as $tableName) {
            if (Schema::hasColumn($tableName, 'batch_no')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('batch_no'));
            }
        }
    }
};
