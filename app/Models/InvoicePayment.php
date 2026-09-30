<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = ['invoice_id', 'payment_date', 'amount', 'method', 'reference_no', 'notes', 'cash_transaction_id', 'created_by'];
    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function cashTransaction() { return $this->belongsTo(CashTransaction::class); }
}
