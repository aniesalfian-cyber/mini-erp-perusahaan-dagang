<?php

namespace App\Services;

use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\DB;

class CreditCheckService
{
    public function checkCreditAvailable(int $customerId, float $newOrderAmount): bool
    {
        $customer = Customer::findOrFail($customerId);
        $projectedCredit = (float)$customer->current_credit + $newOrderAmount;
        return $projectedCredit <= (float)$customer->credit_limit;
    }

    public function assertCanPlaceOrder(int $customerId, float $newOrderAmount): void
    {
        $customer = Customer::findOrFail($customerId);
        $projectedCredit = (float)$customer->current_credit + $newOrderAmount;

        if ($projectedCredit > (float)$customer->credit_limit) {
            $overlimit = $projectedCredit - (float)$customer->credit_limit;
            $limitStr = number_format((float)$customer->credit_limit, 0, ',', '.');
            $currentStr = number_format((float)$customer->current_credit, 0, ',', '.');
            $orderStr = number_format($newOrderAmount, 0, ',', '.');
            $overStr = number_format($overlimit, 0, ',', '.');

            throw new Exception("Transaksi melampaui limit kredit pelanggan {$customer->name}! Plafon: Rp {$limitStr}, Piutang Berjalan: Rp {$currentStr}, Nilai Pesanan Baru: Rp {$orderStr}, Kelebihan: Rp {$overStr}. Harap lunasi piutang terlebih dahulu atau ajukan penambahan plafon ke Accounting/Owner.");
        }
    }

    public function increaseCredit(int $customerId, float $amount): void
    {
        DB::transaction(function () use ($customerId, $amount) {
            $customer = Customer::where('id', $customerId)->lockForUpdate()->firstOrFail();
            $customer->current_credit = (float)$customer->current_credit + $amount;
            $customer->save();
        });
    }

    public function decreaseCredit(int $customerId, float $amount): void
    {
        DB::transaction(function () use ($customerId, $amount) {
            $customer = Customer::where('id', $customerId)->lockForUpdate()->firstOrFail();
            $newCredit = max(0, (float)$customer->current_credit - $amount);
            $customer->current_credit = $newCredit;
            $customer->save();
        });
    }
}
