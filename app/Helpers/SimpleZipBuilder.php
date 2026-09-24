<?php

namespace App\Helpers;

class SimpleZipBuilder
{
    /**
     * Builds a ZIP archive in memory from $files (filename => binary content), without
     * the ZipArchive extension (ext-zip is not reliably enabled in every environment).
     * Uses only crc32()/gzdeflate(), which ship with PHP's near-universal zlib support.
     */
    public static function build(array $files): string
    {
        $localEntries = '';
        $centralDirectory = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $name => $data) {
            $crc = crc32($data);
            $uncompressedSize = strlen($data);
            $compressed = gzdeflate($data, 6);
            $compressedSize = strlen($compressed);
            $nameLength = strlen($name);

            $localHeader = "\x50\x4b\x03\x04"
                . pack('v', 20)
                . pack('v', 0)
                . pack('v', 8)
                . pack('V', 0)
                . pack('V', $crc)
                . pack('V', $compressedSize)
                . pack('V', $uncompressedSize)
                . pack('v', $nameLength)
                . pack('v', 0)
                . $name;

            $localEntries .= $localHeader . $compressed;

            $centralDirectory .= "\x50\x4b\x01\x02"
                . pack('v', 20)
                . pack('v', 20)
                . pack('v', 0)
                . pack('v', 8)
                . pack('V', 0)
                . pack('V', $crc)
                . pack('V', $compressedSize)
                . pack('V', $uncompressedSize)
                . pack('v', $nameLength)
                . pack('v', 0)
                . pack('v', 0)
                . pack('v', 0)
                . pack('v', 0)
                . pack('V', 32)
                . pack('V', $offset)
                . $name;

            $offset += strlen($localHeader) + $compressedSize;
            $count++;
        }

        $endOfCentralDirectory = "\x50\x4b\x05\x06"
            . pack('v', 0)
            . pack('v', 0)
            . pack('v', $count)
            . pack('v', $count)
            . pack('V', strlen($centralDirectory))
            . pack('V', $offset)
            . pack('v', 0);

        return $localEntries . $centralDirectory . $endOfCentralDirectory;
    }

    /**
     * Given a list of ['filename' => ..., 'binary' => ...] entries, de-duplicates
     * filenames (appending -2, -3, ...) and returns them as a filename => binary map
     * ready for build().
     */
    public static function dedupeNames(array $entries): array
    {
        $usedNames = [];
        $zipEntries = [];

        foreach ($entries as $entry) {
            $name = $entry['filename'];
            $suffix = 1;
            while (in_array($name, $usedNames, true)) {
                $name = preg_replace('/\.pdf$/', '', $entry['filename']) . '-' . (++$suffix) . '.pdf';
            }
            $usedNames[] = $name;
            $zipEntries[$name] = $entry['binary'];
        }

        return $zipEntries;
    }
}
