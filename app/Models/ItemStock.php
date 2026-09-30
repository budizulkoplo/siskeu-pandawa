<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    protected $fillable = ['item_id', 'location_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:3'];
    public function item() { return $this->belongsTo(Item::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
