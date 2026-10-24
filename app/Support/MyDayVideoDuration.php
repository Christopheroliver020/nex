<?php

namespace App\Support;

class MyDayVideoDuration
{
    /** Return an MP4/MOV/WebM duration in seconds, or null if unreadable. */
    public static function seconds(string $path): ?float
    {
        $handle = @fopen($path, 'rb');
        $fileSize = @filesize($path);

        if (!$handle || !$fileSize) {
            if (is_resource($handle)) fclose($handle);
            return null;
        }

        try {
            $signature = fread($handle, 4);
            rewind($handle);
            if ($signature === "\x1A\x45\xDF\xA3") {
                return self::webmSeconds($handle, (int) $fileSize);
            }

            $movieHeader = self::findBox($handle, (int) $fileSize, 'mvhd');
            if (!$movieHeader) return null;

            fseek($handle, $movieHeader['payload']);
            $versionFlags = fread($handle, 4);
            if (strlen($versionFlags) !== 4) return null;

            $version = ord($versionFlags[0]);
            if ($version === 0) {
                $fields = fread($handle, 16);
                if (strlen($fields) !== 16) return null;
                $values = unpack('Ncreated/Nmodified/Ntimescale/Nduration', $fields);
                $timescale = (int) $values['timescale'];
                $duration = (int) $values['duration'];
            } elseif ($version === 1) {
                $fields = fread($handle, 28);
                if (strlen($fields) !== 28) return null;
                $values = unpack('NcreatedHigh/NcreatedLow/NmodifiedHigh/NmodifiedLow/Ntimescale/NdurationHigh/NdurationLow', $fields);
                $timescale = (int) $values['timescale'];
                $duration = ((int) $values['durationHigh'] * 4294967296) + (int) $values['durationLow'];
            } else {
                return null;
            }

            return $timescale > 0 && $duration > 0 ? $duration / $timescale : null;
        } finally {
            fclose($handle);
        }
    }

    private static function webmSeconds($handle, int $fileSize): ?float
    {
        while (ftell($handle) < $fileSize) {
            $element = self::readEbmlElement($handle, $fileSize);
            if (!$element) return null;

            if ($element['id'] === 0x18538067) {
                while (ftell($handle) < $element['end']) {
                    $child = self::readEbmlElement($handle, $element['end']);
                    if (!$child) return null;

                    if ($child['id'] === 0x1549A966) {
                        $timecodeScale = 1000000;
                        $duration = null;

                        while (ftell($handle) < $child['end']) {
                            $info = self::readEbmlElement($handle, $child['end']);
                            if (!$info) return null;

                            if ($info['id'] === 0x2AD7B1) {
                                $timecodeScale = self::readEbmlUnsigned($handle, $info['size']);
                            } elseif ($info['id'] === 0x4489) {
                                $value = fread($handle, $info['size']);
                                if (strlen($value) === 4) $duration = unpack('Gvalue', $value)['value'];
                                elseif (strlen($value) === 8) $duration = unpack('Evalue', $value)['value'];
                            }

                            fseek($handle, $info['end']);
                        }

                        return $duration !== null && $timecodeScale > 0
                            ? ($duration * $timecodeScale) / 1000000000
                            : null;
                    }

                    fseek($handle, $child['end']);
                }

                return null;
            }

            fseek($handle, $element['end']);
        }

        return null;
    }

    private static function readEbmlElement($handle, int $rangeEnd): ?array
    {
        if (ftell($handle) >= $rangeEnd) return null;

        $firstIdByte = fread($handle, 1);
        if (strlen($firstIdByte) !== 1) return null;
        $idFirst = ord($firstIdByte);
        $idLength = self::vintLength($idFirst);
        if ($idLength < 1 || $idLength > 4) return null;

        $idBytes = $firstIdByte . ($idLength > 1 ? fread($handle, $idLength - 1) : '');
        if (strlen($idBytes) !== $idLength) return null;
        $id = 0;
        for ($index = 0; $index < $idLength; $index++) $id = ($id << 8) | ord($idBytes[$index]);

        $sizeFirstByte = fread($handle, 1);
        if (strlen($sizeFirstByte) !== 1) return null;
        $sizeLength = self::vintLength(ord($sizeFirstByte));
        if ($sizeLength < 1 || $sizeLength > 8) return null;

        $sizeBytes = $sizeFirstByte . ($sizeLength > 1 ? fread($handle, $sizeLength - 1) : '');
        if (strlen($sizeBytes) !== $sizeLength) return null;
        $size = ord($sizeBytes[0]) & ((1 << (8 - $sizeLength)) - 1);
        for ($index = 1; $index < $sizeLength; $index++) $size = ($size * 256) + ord($sizeBytes[$index]);

        $unknownSize = $size === (2 ** (7 * $sizeLength) - 1);
        $payload = ftell($handle);
        $end = $unknownSize ? $rangeEnd : $payload + $size;
        if ($end < $payload || $end > $rangeEnd) return null;

        return ['id' => $id, 'size' => $end - $payload, 'end' => $end];
    }

    private static function vintLength(int $firstByte): int
    {
        for ($length = 1, $mask = 0x80; $length <= 8; $length++, $mask >>= 1) {
            if (($firstByte & $mask) !== 0) return $length;
        }

        return 0;
    }

    private static function readEbmlUnsigned($handle, int $length): int
    {
        if ($length < 1 || $length > 8) return 0;
        $bytes = fread($handle, $length);
        if (strlen($bytes) !== $length) return 0;

        $value = 0;
        for ($index = 0; $index < $length; $index++) $value = ($value * 256) + ord($bytes[$index]);

        return $value;
    }

    /** Find an MP4 atom, descending into the movie atom when looking for mvhd. */
    private static function findBox($handle, int $rangeEnd, string $wanted): ?array
    {
        while (ftell($handle) + 8 <= $rangeEnd) {
            $start = ftell($handle);
            $header = fread($handle, 8);
            if (strlen($header) !== 8) return null;

            $size = unpack('Nsize', substr($header, 0, 4))['size'];
            $type = substr($header, 4, 4);
            $headerSize = 8;

            if ($size === 1) {
                $extended = fread($handle, 8);
                if (strlen($extended) !== 8) return null;
                $parts = unpack('Nhigh/Nlow', $extended);
                $size = ((int) $parts['high'] * 4294967296) + (int) $parts['low'];
                $headerSize = 16;
            } elseif ($size === 0) {
                $size = $rangeEnd - $start;
            }

            if ($size < $headerSize || $start + $size > $rangeEnd) return null;

            $box = [
                'payload' => $start + $headerSize,
                'end' => $start + $size,
            ];

            if ($type === $wanted) return $box;

            if ($type === 'moov' && $wanted === 'mvhd') {
                fseek($handle, $box['payload']);
                $found = self::findBox($handle, $box['end'], $wanted);
                if ($found) return $found;
                fseek($handle, $box['end']);
            } else {
                fseek($handle, $box['end']);
            }
        }

        return null;
    }
}
