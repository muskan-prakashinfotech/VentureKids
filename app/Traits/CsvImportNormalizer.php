<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;

trait CsvImportNormalizer
{
    private function normalizeCsvRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }
            $row[$key] = $value;
        }
        return $row;
    }

    private function normalizeCsvGender($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $val = trim((string) $value);
        if ($val === '') {
            return null;
        }
        return ucfirst(strtolower($val));
    }

    private function normalizeCsvDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $val = trim((string) $value);
        if ($val === '') {
            return null;
        }

        if (is_numeric($val)) {
            $date = Carbon::create(1899, 12, 30)->addDays((int) $val);
            return $date->format('d-m-Y');
        }

        $formats = [
            'd-m-Y', 'd/m/Y', 'd.m.Y',
            'j-n-Y', 'j/n/Y', 'j.n.Y',
            'Y-m-d', 'Y/m/d', 'Y.m.d',
            'm/d/Y', 'm-d-Y', 'm.d.Y',
            'n/j/Y', 'n-j-Y', 'n.j.Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $val);
                if ($date && $date->format($format) === $val) {
                    return $date->format('d-m-Y');
                }
            } catch (\Exception $e) {
                // try next format
            }
        }

        try {
            $date = Carbon::parse($val);
            return $date->format('d-m-Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function makeUniqueUsername(string $base, array &$used = []): string
    {
        $base = trim($base);
        if ($base === '') {
            $base = 'student';
        }

        $candidate = $base;
        $i = 0;
        while (User::where('username', $candidate)->exists() || in_array($candidate, $used, true)) {
            $i++;
            $candidate = $base.$i;
            if ($i > 9999) {
                $candidate = $base.Str::random(4);
                break;
            }
        }

        $used[] = $candidate;
        return $candidate;
    }
}
