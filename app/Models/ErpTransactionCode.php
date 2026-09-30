<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpTransactionCode extends Model
{
    protected $fillable = ['code', 'name', 'direction', 'coa_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function coa() { return $this->belongsTo(Coa::class); }
}
