<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLedger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Reservasi stok saat Sales Order dikonfirmasi oleh Marketing.
     */
    public function reserveStock(int $itemId, float $qty, int $userId, string $referenceNo, ?int $soId = null): void
    {
        DB::transaction(function () use ($itemId, $qty, $userId, $referenceNo, $soId) {
            $balance = StockBalance::where('item_id', $itemId)->lockForUpdate()->first();
            $item = Item::findOrFail($itemId);

            if (!$balance) {
                $balance = StockBalance::create([
                    'item_id' => $itemId,
                    'on_hand' => 0,
                    'reserved' => 0,
                    'available' => 0,
                ]);
            }

            if ((float)$balance->available < $qty) {
                $availStr = number_format((float)$balance->available, 2);
                $reqStr = number_format($qty, 2);
                throw new Exception("Stok tersedia tidak mencukupi untuk {$item->name} ({$item->code}). Diminta: {$reqStr} {$item->unit}, Tersedia: {$availStr} {$item->unit}.");
            }

            $newReserved = (float)$balance->reserved + $qty;
            $newAvailable = (float)$balance->on_hand - $newReserved;

            $balance->reserved = $newReserved;
            $balance->available = $newAvailable;
            $balance->save();
        });
    }

    /**
     * Batalkan reservasi stok jika Sales Order dibatalkan.
     */
    public function releaseReservedStock(int $itemId, float $qty, int $userId, string $referenceNo, ?int $soId = null): void
    {
        DB::transaction(function () use ($itemId, $qty, $userId, $referenceNo, $soId) {
            $balance = StockBalance::where('item_id', $itemId)->lockForUpdate()->firstOrFail();

            $newReserved = max(0, (float)$balance->reserved - $qty);
            $newAvailable = (float)$balance->on_hand - $newReserved;

            $balance->reserved = $newReserved;
            $balance->available = $newAvailable;
            $balance->save();
        });
    }

    /**
     * Kurangi stok saat Surat Jalan (Delivery Order) dikirim oleh Logistik.
     */
    public function shipStock(int $itemId, float $qty, int $userId, string $referenceNo, int $doId, ?string $notes = null): void
    {
        DB::transaction(function () use ($itemId, $qty, $userId, $referenceNo, $doId, $notes) {
            $balance = StockBalance::where('item_id', $itemId)->lockForUpdate()->firstOrFail();
            $item = Item::findOrFail($itemId);

            if ((float)$balance->on_hand < $qty) {
                throw new Exception("Stok fisik di gudang tidak mencukupi untuk pengiriman {$item->name}!");
            }

            $newOnHand = (float)$balance->on_hand - $qty;
            $newReserved = max(0, (float)$balance->reserved - $qty);
            $newAvailable = $newOnHand - $newReserved;

            $balance->on_hand = $newOnHand;
            $balance->reserved = $newReserved;
            $balance->available = $newAvailable;
            $balance->save();

            StockLedger::create([
                'item_id' => $itemId,
                'transaction_type' => 'SO_DELIVERY',
                'reference_type' => 'DELIVERY_ORDER',
                'reference_id' => $doId,
                'reference_no' => $referenceNo,
                'qty_in' => 0,
                'qty_out' => $qty,
                'balance_after' => $newOnHand,
                'notes' => $notes ?? "Pengiriman Surat Jalan {$referenceNo}",
                'user_id' => $userId,
                'created_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Tambah stok saat Goods Receipt (Nota Penerimaan Barang) diterima & disetujui Logistik.
     */
    public function receiveStock(int $itemId, float $qty, int $userId, string $referenceNo, int $grnId, ?string $notes = null): void
    {
        DB::transaction(function () use ($itemId, $qty, $userId, $referenceNo, $grnId, $notes) {
            $balance = StockBalance::where('item_id', $itemId)->lockForUpdate()->first();

            if (!$balance) {
                $balance = StockBalance::create([
                    'item_id' => $itemId,
                    'on_hand' => 0,
                    'reserved' => 0,
                    'available' => 0,
                ]);
            }

            $newOnHand = (float)$balance->on_hand + $qty;
            $newAvailable = $newOnHand - (float)$balance->reserved;

            $balance->on_hand = $newOnHand;
            $balance->available = $newAvailable;
            $balance->save();

            StockLedger::create([
                'item_id' => $itemId,
                'transaction_type' => 'PO_RECEIPT',
                'reference_type' => 'GOODS_RECEIPT',
                'reference_id' => $grnId,
                'reference_no' => $referenceNo,
                'qty_in' => $qty,
                'qty_out' => 0,
                'balance_after' => $newOnHand,
                'notes' => $notes ?? "Penerimaan barang GRN {$referenceNo}",
                'user_id' => $userId,
                'created_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Penyesuaian stok opname manual (khusus Admin / Owner).
     */
    public function adjustStock(int $itemId, float $newOnHand, string $reason, int $userId): void
    {
        DB::transaction(function () use ($itemId, $newOnHand, $reason, $userId) {
            $balance = StockBalance::where('item_id', $itemId)->lockForUpdate()->firstOrFail();
            $oldOnHand = (float)$balance->on_hand;
            $diff = $newOnHand - $oldOnHand;

            $balance->on_hand = $newOnHand;
            $balance->available = $newOnHand - (float)$balance->reserved;
            $balance->save();

            $qtyIn = $diff > 0 ? $diff : 0;
            $qtyOut = $diff < 0 ? abs($diff) : 0;
            $type = $diff >= 0 ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT';

            StockLedger::create([
                'item_id' => $itemId,
                'transaction_type' => $type,
                'reference_type' => 'STOCK_OPNAME',
                'reference_id' => $itemId,
                'reference_no' => 'ADJ-' . date('YmdHis'),
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'balance_after' => $newOnHand,
                'notes' => "Penyesuaian stok opname: {$reason}",
                'user_id' => $userId,
                'created_at' => Carbon::now(),
            ]);
        });
    }
}
