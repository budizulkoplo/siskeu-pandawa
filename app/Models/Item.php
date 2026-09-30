<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;
    protected $fillable = ['code', 'name', 'category_id', 'unit', 'purchase_price', 'sale_price', 'minimum_stock', 'supplier_id', 'is_active'];
    protected $casts = ['purchase_price' => 'decimal:2', 'sale_price' => 'decimal:2', 'is_active' => 'boolean'];
    public function category() { return $this->belongsTo(Category::class); }
    public function stocks() { return $this->hasMany(ItemStock::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
}
