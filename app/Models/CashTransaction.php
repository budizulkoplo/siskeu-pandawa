<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashTransaction extends Model
{
    use SoftDeletes;
    protected $fillable = ['transaction_no', 'transaction_date', 'direction', 'coa_id', 'erp_code_id', 'amount', 'description', 'reference_type', 'reference_id', 'created_by', 'updated_by'];
    protected $casts = ['transaction_date' => 'date', 'amount' => 'decimal:2'];
    public function coa() { return $this->belongsTo(Coa::class); }
    public function erpCode() { return $this->belongsTo(ErpTransactionCode::class, 'erp_code_id'); }
}
