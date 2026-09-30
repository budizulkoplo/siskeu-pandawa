<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $fillable = ['code', 'name', 'type', 'parent_id', 'level'];
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
}
