<?php

namespace App\Traits;

trait CsvFIleupload
{
    public function getCsvAsArray($file, $keyField = null)
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        if (empty($filePath) || !is_readable($filePath)) {
            return [];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $rowKeys = fgetcsv($handle);
        if ($rowKeys === false) {
            fclose($handle);
            return [];
        }

        // Remove UTF-8 BOM from the first header when present.
        if (!empty($rowKeys[0])) {
            $rowKeys[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rowKeys[0]);
        }

        $formattedData = [];
        $expectedColumnCount = count($rowKeys);

        while (($row = fgetcsv($handle)) !== false) {
            // Skip completely empty rows.
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            // Normalize row width to avoid array_combine ValueError.
            $rowCount = count($row);
            if ($rowCount < $expectedColumnCount) {
                $row = array_pad($row, $expectedColumnCount, null);
            } elseif ($rowCount > $expectedColumnCount) {
                $row = array_slice($row, 0, $expectedColumnCount);
            }

            $associatedRowData = array_combine($rowKeys, $row);
            if (empty($keyField)) {
                $formattedData[] = $associatedRowData;
            } else {
                $formattedData[$associatedRowData[$keyField]] = $associatedRowData;
            }
        }

        fclose($handle);

        return $formattedData;
    }
}
