<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItem extends Model
{
    protected $fillable = ['delivery_order_id', 'item_id', 'description', 'quantity', 'unit'];
    protected $casts = ['quantity' => 'decimal:3'];
    public function item() { return $this->belongsTo(Item::class); }
}
