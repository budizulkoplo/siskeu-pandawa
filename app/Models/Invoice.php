<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = ['invoice_no', 'invoice_date', 'due_date', 'customer_id', 'sale_id', 'project_id', 'subtotal', 'discount', 'tax', 'grand_total', 'paid_total', 'payment_term', 'status', 'notes', 'terbilang', 'created_by', 'updated_by'];
    protected $casts = ['invoice_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'grand_total' => 'decimal:2', 'paid_total' => 'decimal:2'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function payments() { return $this->hasMany(InvoicePayment::class); }
    public function receivable() { return $this->hasOne(AccountReceivable::class); }
    public function deliveryOrders() { return $this->hasMany(DeliveryOrder::class); }
    public function receipts() { return $this->hasMany(InvoiceReceipt::class); }
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'cancelled') return 'Cancelled';
        if ((float) $this->paid_total >= (float) $this->grand_total) return 'Lunas';
        if ($this->due_date && $this->due_date->isPast()) return 'Overdue';
        if ((float) $this->paid_total > 0) return 'Sebagian Dibayar';
        return $this->status === 'draft' ? 'Draft' : 'Belum Dibayar';
    }
}
