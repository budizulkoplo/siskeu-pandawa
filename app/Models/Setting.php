<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['nama_perusahaan', 'alamat', 'telepon', 'path_logo', 'npwp', 'bank_name', 'bank_account', 'bank_account_name'];
}
