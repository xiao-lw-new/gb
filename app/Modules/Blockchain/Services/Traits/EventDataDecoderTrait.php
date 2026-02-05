<?php

namespace App\Modules\Blockchain\Services\Traits;

use kornrunner\Keccak;
use Illuminate\Support\Facades\Log;

trait EventDataDecoderTrait
{
    /**
     * Decode non-indexed event parameters (supports static tuple and tuple[] of uint/bool/address)
     */
    public function decodeNonIndexedParameters(string $data, array $params, string $logChannel = 'chain_event'): array
    {
        $decoded = [];
        $hex = strtolower($data);
        $hex = preg_replace('/^0x/', '', $hex);
        if (!is_string($hex)) {
            return $decoded;
        }

        $word = fn (int $wordIndex): string => substr($hex, $wordIndex * 64, 64);

        $headIndex = 0;
        $dynamicQueue = [];

        foreach ($params as $param) {
            $name = $param['name'] ?? '';

            if ($this->isDynamicType($param)) {
                $offsetBytes = $this->hexToDec($word($headIndex));
                $dynamicQueue[] = ['param' => $param, 'name' => $name, 'offsetBytes' => $offsetBytes];
                $headIndex += 1;
                continue;
            }

            [$val, $used] = $this->decodeStaticAt($word, $headIndex, $param);
            if ($name !== '') {
                $decoded[$name] = $val;
            }
            $headIndex += $used;
        }

        foreach ($dynamicQueue as $dyn) {
            $param = $dyn['param'];
            $name = $dyn['name'];
            $offsetBytes = $dyn['offsetBytes'];
            $offsetWords = (int) floor(((int) $offsetBytes) / 32);

            if (($param['type'] ?? '') === 'tuple[]') {
                $len = (int) $this->hexToDec($word($offsetWords));
                $components = $param['components'] ?? [];
                $elemWords = $this->staticWordCount($components);
                $items = [];
                $base = $offsetWords + 1;
                for ($i = 0; $i < $len; $i++) {
                    [$row] = $this->decodeTupleAt($word, $base + ($i * $elemWords), $components);
                    $items[] = $row;
                }
                if ($name !== '') {
                    $decoded[$name] = $items;
                }
            }
        }

        return $decoded;
    }

    /**
     * Decode indexed event parameters
     */
    public function decodeEventData(object $log, array $abi): ?array
    {
        $topic = $log->topics[0] ?? null;
        if ($topic !== null) {
            $topic = strtolower((string) $topic);
        }
        $eventDef = null;

        foreach ($abi as $item) {
            if (isset($item['type']) && $item['type'] === 'event') {
                $types = array_map(fn ($in) => $this->canonicalType($in), $item['inputs'] ?? []);
                $sig = $item['name'] . '(' . implode(',', $types) . ')';
                $expectedTopic = '0x' . Keccak::hash($sig, 256);
                if ($expectedTopic === $topic) {
                    $eventDef = $item;
                    break;
                }
            }
        }

        if (!$eventDef) return null;

        $decoded = ['event' => $eventDef['name']];
        $indexed = array_filter($eventDef['inputs'], fn($i) => $i['indexed'] ?? false);
        $nonIndexed = array_filter($eventDef['inputs'], fn($i) => !($i['indexed'] ?? false));

        $i = 1;
        foreach ($indexed as $param) {
            if (isset($log->topics[$i])) {
                $val = $log->topics[$i];
                if ($param['type'] === 'address') $val = '0x' . substr($val, -40);
                elseif (str_starts_with($param['type'], 'uint')) $val = (string)hexdec($val);
                $decoded[$param['name']] = $val;
            }
            $i++;
        }

        if (isset($log->data) && $log->data !== '0x') {
            $data = substr($log->data, 2);
            $extra = $this->decodeNonIndexedParameters($data, array_values($nonIndexed));
            foreach ($extra as $k => $v) $decoded[$k] = $v;
        }

        return $decoded;
    }

    private function canonicalType(array $input): string
    {
        $type = (string) ($input['type'] ?? '');
        if ($type === '') {
            return $type;
        }

        if ($type === 'tuple' || str_starts_with($type, 'tuple')) {
            $suffix = '';
            if (str_ends_with($type, '[]')) {
                $suffix = '[]';
            }
            $components = $input['components'] ?? [];
            if (!is_array($components)) {
                $components = [];
            }
            $inner = implode(',', array_map(fn ($c) => $this->canonicalType($c), $components));
            return '(' . $inner . ')' . $suffix;
        }

        return $type;
    }

    private function isDynamicType(array $param): bool
    {
        $type = (string) ($param['type'] ?? '');
        if ($type === 'string' || $type === 'bytes') {
            return true;
        }
        if ($type === 'tuple[]') {
            return true;
        }
        return false;
    }

    private function staticWordCount(array $components): int
    {
        $count = 0;
        foreach ($components as $comp) {
            $type = (string) ($comp['type'] ?? '');
            if ($type === 'tuple') {
                $count += $this->staticWordCount($comp['components'] ?? []);
            } else {
                $count += 1;
            }
        }
        return $count;
    }

    private function decodeStaticAt(callable $word, int $startIndex, array $param): array
    {
        $type = (string) ($param['type'] ?? '');
        if ($type === 'tuple') {
            return $this->decodeTupleAt($word, $startIndex, $param['components'] ?? []);
        }

        $val = $this->decodePrimitive($type, $word($startIndex));
        return [$val, 1];
    }

    private function decodeTupleAt(callable $word, int $startIndex, array $components): array
    {
        $values = [];
        $idx = $startIndex;
        foreach ($components as $comp) {
            $name = $comp['name'] ?? '';
            $type = (string) ($comp['type'] ?? '');
            if ($type === 'tuple') {
                [$nested, $used] = $this->decodeTupleAt($word, $idx, $comp['components'] ?? []);
                if ($name !== '') {
                    $values[$name] = $nested;
                }
                $idx += $used;
                continue;
            }
            $v = $this->decodePrimitive($type, $word($idx));
            if ($name !== '') {
                $values[$name] = $v;
            }
            $idx += 1;
        }
        return [$values, $idx - $startIndex];
    }

    private function decodePrimitive(string $type, string $wordHex)
    {
        if ($type === 'address') {
            return '0x' . substr($wordHex, 24, 40);
        }
        if (str_starts_with($type, 'uint')) {
            return $this->hexToDec($wordHex);
        }
        if ($type === 'bool') {
            return hexdec($wordHex) > 0;
        }
        return $wordHex;
    }

    private function hexToDec(string $wordHex): string
    {
        $wordHex = ltrim($wordHex, '0');
        if ($wordHex === '') {
            return '0';
        }
        if (function_exists('bcadd')) {
            $dec = '0';
            foreach (str_split($wordHex) as $ch) {
                $dec = bcmul($dec, '16', 0);
                $dec = bcadd($dec, (string) hexdec($ch), 0);
            }
            return $dec;
        }
        return (string) hexdec(substr($wordHex, -15));
    }
}

