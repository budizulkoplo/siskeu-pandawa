<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ItemStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function move(array $data): InventoryMovement
    {
        return DB::transaction(function () use ($data) {
            $stock = ItemStock::query()
                ->where('item_id', $data['item_id'])
                ->where('location_id', $data['location_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = ItemStock::create([
                    'item_id' => $data['item_id'],
                    'location_id' => $data['location_id'],
                    'quantity' => 0,
                ]);
                $stock->refresh();
            }

            $quantity = (float) $data['quantity'];
            $balance = (float) $stock->quantity + ($data['direction'] === 'in' ? $quantity : -$quantity);
            if ($balance < 0) {
                throw ValidationException::withMessages([
                    'items' => 'Stok tidak mencukupi untuk barang yang dipilih.',
                ]);
            }

            $stock->update(['quantity' => $balance]);

            return InventoryMovement::create([
                ...$data,
                'balance_after' => $balance,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
