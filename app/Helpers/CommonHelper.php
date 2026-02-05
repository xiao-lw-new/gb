<?php

namespace App\Helpers;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Log;

class CommonHelper
{
    public static function toContractValue($number, int $decimals = 18): string
    {
        try {
            // 记录转换开始

            // 将输入转换为 BigDecimal
            $bigDecimal = BigDecimal::of($number);

            // 计算 10 的 decimals 次方
            $multiplier = BigDecimal::of(10)->power($decimals);

            // 相乘并四舍五入到整数
            $result = $bigDecimal->multipliedBy($multiplier)->toScale(0, RoundingMode::HALF_UP);

            // 转换为字符串
            $resultString = (string)$result;

//            Log::info('Conversion successful', [
//                'input' => $number,
//                'output' => $resultString,
//                'decimals' => $decimals
//            ]);

            return $resultString;
        } catch (\Exception $e) {
            Log::error('Conversion error', [
                'time' => now()->format('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
                'input' => $number,
                'decimals' => $decimals
            ]);
            throw new \InvalidArgumentException("Invalid number format: {$number}");
        }
    }

    /**
     * 将合约大数字转换回 double
     * @param string $contractValue 合约值
     * @param int $decimals 代币精度
     * @return string 保持精确度的字符串
     */
    public static function fromContractValue(string $contractValue, int $decimals = 18): string
    {
        try {

            // 将输入转换为 BigDecimal
            $bigDecimal = BigDecimal::of($contractValue);

            // 计算 10 的 decimals 次方
            $divisor = BigDecimal::of(10)->power($decimals);

            // 相除并保留适当的小数位
            $result = $bigDecimal->dividedBy($divisor, $decimals, RoundingMode::HALF_UP);

            // 转换为字符串
            $resultString = (string)$result;


            return $resultString;
        } catch (\Exception $e) {
            Log::error('Conversion error', [
                'time' => now()->format('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
                'input' => $contractValue,
                'decimals' => $decimals
            ]);
            throw new \InvalidArgumentException("Invalid contract value: {$contractValue}");
        }
    }
}

