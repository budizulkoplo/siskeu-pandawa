<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $fillable = ['auditable_type', 'auditable_id', 'action', 'old_values', 'new_values', 'description', 'user_id', 'ip_address'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
    public function user() { return $this->belongsTo(User::class); }
}
