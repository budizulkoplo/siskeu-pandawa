<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;
    protected $fillable = ['code', 'name', 'address', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
