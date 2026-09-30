<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = ['invoice_no', 'transaction_date', 'customer_name', 'customer_id', 'location_id', 'due_date', 'subtotal', 'discount', 'tax', 'total', 'payment_method', 'payment_term', 'status', 'notes', 'created_by', 'updated_by'];
    protected $casts = ['transaction_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2'];
    public function details() { return $this->hasMany(SaleDetail::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
