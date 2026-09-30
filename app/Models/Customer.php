<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = ['code', 'name', 'address', 'phone', 'email', 'npwp', 'pic', 'status', 'notes'];
    public function invoices() { return $this->hasMany(Invoice::class); }
}
