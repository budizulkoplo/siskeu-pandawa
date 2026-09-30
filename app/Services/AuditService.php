<?php

namespace App\Services;

use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function record(Model $model, string $action, ?array $old = null, ?array $new = null, ?string $description = null): TransactionLog
    {
        return TransactionLog::create([
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'description' => $description,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);
    }
}
