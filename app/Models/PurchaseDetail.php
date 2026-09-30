<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $fillable = ['purchase_id', 'item_id', 'batch_no', 'quantity', 'price', 'total'];
    protected $casts = ['quantity' => 'decimal:3', 'price' => 'decimal:2', 'total' => 'decimal:2'];
    public function item() { return $this->belongsTo(Item::class); }
}
