<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = ['code', 'name', 'phone', 'email', 'npwp', 'pic', 'status', 'notes', 'address'];
}
