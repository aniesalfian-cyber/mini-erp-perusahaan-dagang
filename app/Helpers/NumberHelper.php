<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Konversi angka nominal uang menjadi teks terbilang bahasa Indonesia.
     */
    public static function terbilang(float|int|string $number): string
    {
        $number = abs((int)$number);
        if ($number === 0) {
            return 'Nol Rupiah';
        }

        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            $text = $words[$number];
        } elseif ($number < 20) {
            $text = self::terbilangRaw($number - 10) . ' Belas';
        } elseif ($number < 100) {
            $text = self::terbilangRaw(intdiv($number, 10)) . ' Puluh' . ($number % 10 > 0 ? ' ' . self::terbilangRaw($number % 10) : '');
        } elseif ($number < 200) {
            $text = 'Seratus' . ($number - 100 > 0 ? ' ' . self::terbilangRaw($number - 100) : '');
        } elseif ($number < 1000) {
            $text = self::terbilangRaw(intdiv($number, 100)) . ' Ratus' . ($number % 100 > 0 ? ' ' . self::terbilangRaw($number % 100) : '');
        } elseif ($number < 2000) {
            $text = 'Seribu' . ($number - 1000 > 0 ? ' ' . self::terbilangRaw($number - 1000) : '');
        } elseif ($number < 1000000) {
            $text = self::terbilangRaw(intdiv($number, 1000)) . ' Ribu' . ($number % 1000 > 0 ? ' ' . self::terbilangRaw($number % 1000) : '');
        } elseif ($number < 1000000000) {
            $text = self::terbilangRaw(intdiv($number, 1000000)) . ' Juta' . ($number % 1000000 > 0 ? ' ' . self::terbilangRaw($number % 1000000) : '');
        } elseif ($number < 1000000000000) {
            $text = self::terbilangRaw(intdiv($number, 1000000000)) . ' Miliar' . ($number % 1000000000 > 0 ? ' ' . self::terbilangRaw($number % 1000000000) : '');
        } else {
            $text = (string)$number;
        }

        return trim(preg_replace('/\s+/', ' ', $text)) . ' Rupiah';
    }

    private static function terbilangRaw(int $number): string
    {
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            return $words[$number];
        }
        if ($number < 20) {
            return self::terbilangRaw($number - 10) . ' Belas';
        }
        if ($number < 100) {
            return self::terbilangRaw(intdiv($number, 10)) . ' Puluh' . ($number % 10 > 0 ? ' ' . self::terbilangRaw($number % 10) : '');
        }
        if ($number < 200) {
            return 'Seratus' . ($number - 100 > 0 ? ' ' . self::terbilangRaw($number - 100) : '');
        }
        if ($number < 1000) {
            return self::terbilangRaw(intdiv($number, 100)) . ' Ratus' . ($number % 100 > 0 ? ' ' . self::terbilangRaw($number % 100) : '');
        }
        if ($number < 2000) {
            return 'Seribu' . ($number - 1000 > 0 ? ' ' . self::terbilangRaw($number - 1000) : '');
        }
        if ($number < 1000000) {
            return self::terbilangRaw(intdiv($number, 1000)) . ' Ribu' . ($number % 1000 > 0 ? ' ' . self::terbilangRaw($number % 1000) : '');
        }
        if ($number < 1000000000) {
            return self::terbilangRaw(intdiv($number, 1000000)) . ' Juta' . ($number % 1000000 > 0 ? ' ' . self::terbilangRaw($number % 1000000) : '');
        }
        if ($number < 1000000000000) {
            return self::terbilangRaw(intdiv($number, 1000000000)) . ' Miliar' . ($number % 1000000000 > 0 ? ' ' . self::terbilangRaw($number % 1000000000) : '');
        }

        return (string)$number;
    }

    /**
     * Format Rupiah standar Indonesia.
     */
    public static function rupiah(float|int|string $amount): string
    {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }
}
