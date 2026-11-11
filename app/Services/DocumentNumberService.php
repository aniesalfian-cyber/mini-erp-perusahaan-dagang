<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate sequential atomic document number.
     * Format: PREFIX-YYYYMM-XXXX (e.g. SO-202611-0001)
     */
    public function generate(string $prefix): string
    {
        $now = Carbon::now();
        $year = (int)$now->format('Y');
        $month = (int)$now->format('n');
        $prefixUpper = strtoupper($prefix);

        return DB::transaction(function () use ($prefixUpper, $year, $month, $now) {
            $sequence = DocumentSequence::where('prefix', $prefixUpper)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = DocumentSequence::create([
                    'prefix' => $prefixUpper,
                    'year' => $year,
                    'month' => $month,
                    'current_number' => 1,
                ]);
                $nextNumber = 1;
            } else {
                $nextNumber = $sequence->current_number + 1;
                $sequence->current_number = $nextNumber;
                $sequence->save();
            }

            $dateSegment = $now->format('Ym');
            $paddedSequence = str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);

            return "{$prefixUpper}-{$dateSegment}-{$paddedSequence}";
        });
    }
}
