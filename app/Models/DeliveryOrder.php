<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use SoftDeletes;
    protected $fillable = ['delivery_no', 'invoice_id', 'customer_id', 'delivery_date', 'shipping_address', 'recipient_name', 'recipient_signature', 'status', 'notes', 'created_by'];
    protected $casts = ['delivery_date' => 'date'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(DeliveryOrderItem::class); }
}
