<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceReceipt extends Model
{
    protected $fillable = ['invoice_id', 'received_date', 'received_by', 'signature_path', 'proof_path', 'notes', 'created_by'];
    protected $casts = ['received_date' => 'date'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
}
