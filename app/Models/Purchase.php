<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;
    protected $fillable = ['invoice_no', 'transaction_date', 'supplier_id', 'location_id', 'supplier_invoice_no', 'due_date', 'subtotal', 'discount', 'tax', 'total', 'payment_term', 'payment_status', 'notes', 'created_by', 'updated_by'];
    protected $casts = ['transaction_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2'];
    public function details() { return $this->hasMany(PurchaseDetail::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function payable() { return $this->hasOne(AccountPayable::class); }
}
