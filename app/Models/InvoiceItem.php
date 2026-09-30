<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'item_id', 'description', 'batch_no', 'quantity', 'unit', 'unit_price', 'discount', 'tax', 'total'];
    protected $casts = ['quantity' => 'decimal:3', 'unit_price' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2'];
    public function item() { return $this->belongsTo(Item::class); }
}
