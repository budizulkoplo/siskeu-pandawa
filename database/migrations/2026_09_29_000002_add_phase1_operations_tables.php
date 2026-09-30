<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('npwp')->nullable();
            $table->string('pic')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        foreach (['suppliers' => ['email' => 'string', 'npwp' => 'string', 'pic' => 'string', 'status' => 'string', 'notes' => 'text'], 'items' => ['minimum_stock' => 'decimal:18,3', 'supplier_id' => 'foreignId']] as $tableName => $columns) {
            foreach ($columns as $column => $type) {
                if (Schema::hasColumn($tableName, $column)) continue;
                Schema::table($tableName, function (Blueprint $table) use ($column, $type) {
                    if ($type === 'text') $table->text($column)->nullable();
                    elseif ($type === 'foreignId') $table->foreignId($column)->nullable();
                    elseif ($type === 'decimal:18,3') $table->decimal($column, 18, 3)->default(0);
                    else $table->string($column)->nullable();
                });
            }
        }
        if (Schema::hasColumn('items', 'supplier_id')) {
            Schema::table('items', fn (Blueprint $table) => $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete());
        }

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('supplier_invoice_no')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('tax', 18, 2)->default(0);
            $table->string('payment_term', 50)->nullable();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('customer_name')->constrained('customers')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('tax', 18, 2)->default(0);
            $table->string('payment_term', 50)->nullable();
            $table->string('status', 20)->default('completed');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 60)->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('project_id')->nullable();
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->decimal('paid_total', 18, 2)->default(0);
            $table->string('payment_term', 50)->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->string('terbilang')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['customer_id', 'due_date', 'status']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('description');
            $table->string('batch_no')->nullable();
            $table->decimal('quantity', 18, 3)->default(0);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->decimal('amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('unpaid');
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });

        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->unique()->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('unpaid');
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('method', 30)->default('transfer');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('cash_transaction_id')->nullable()->constrained('cash_transactions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_no', 60)->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('delivery_date');
            $table->text('shipping_address')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_signature')->nullable();
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 18, 3)->default(0);
            $table->string('unit', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->date('received_date');
            $table->string('received_by');
            $table->string('signature_path')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['invoice_receipts', 'delivery_order_items', 'delivery_orders', 'invoice_payments', 'accounts_payable', 'accounts_receivable', 'invoice_items', 'invoices', 'sales', 'purchases'] as $table) Schema::dropIfExists($table);
        Schema::dropIfExists('customers');
    }
};
