<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = ['item_id', 'location_id', 'movement_type', 'direction', 'quantity', 'unit_price', 'balance_after', 'reference_type', 'reference_id', 'reference_no', 'created_by', 'notes'];
    protected $casts = ['quantity' => 'decimal:3', 'unit_price' => 'decimal:2', 'balance_after' => 'decimal:3'];
    public function item() { return $this->belongsTo(Item::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function user() { return $this->belongsTo(User::class, 'created_by'); }
}
