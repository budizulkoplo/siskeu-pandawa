<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    protected $table = 'accounts_receivable';
    protected $fillable = ['invoice_id', 'customer_id', 'amount', 'paid_amount', 'due_date', 'status'];
    protected $casts = ['amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'due_date' => 'date'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
