<?php

namespace Tai\CurrencyExchangeRate\Agent;

use Bitrix\Main\Diag\Debug;
use Exception;
use Tai\CurrencyExchangeRate\Renderer\StockChartRenderer;
use Throwable;

class RenderAgent
{
    public static function stockChartRender()
    {
        try {
            $scr = new StockChartRenderer();

            $scr
                ->setFilePath(__DIR__ . '/test.png')
                ->setWidth(450)
                ->setHeight(300)
                ->setData(self::stockChartGenerateData(10, 100, 40, 24))
                ->render();
        } catch (Throwable $e) {
            Debug::dumpToFile($e->getMessage());
            Debug::dumpToFile($e->getTraceAsString());
        }

        return __METHOD__ . '();';
    }

    /**
     * Generates stock chart random data. If data cannot be generated with
     * required deviation at steps number than exception is raised.
     * @param float $startRate Start data value
     * @param float $endRate End data value
     * @param float $maxDeviation Max data deviation
     * @param int $steps Number of data points
     * 
     * @return array
     */
    private static function stockChartGenerateData(float $startRate, float $endRate, float $maxDeviation, int $steps): array
    {
        if ($steps < 2) {
            throw new Exception('Steps must be at least 2');
        }

        $data = [];

        $data[] = $startRate;

        $maxDeviation = abs($maxDeviation);
        $defaultDeviation = $maxDeviation / 2;
        $deviation = $defaultDeviation;
        $prevRate = $startRate;
        $sign = 1;

        for ($i = 1; $i < $steps - 1; $i++) {
            if (
                $endRate - $prevRate > $defaultDeviation && $sign > 0
                || $prevRate - $endRate > $defaultDeviation && $sign < 0
            ) {
                $deviation = $maxDeviation;
            } else {
                $deviation = $defaultDeviation;
            }

            $newRate = $prevRate + $sign * $deviation * (rand() / getrandmax());
            $data[] = $newRate;

            $sign *= -1;
            $prevRate = $newRate;
        }
        
        if (abs($endRate - $prevRate) > $maxDeviation) {
            throw new Exception('Could not genarate chart. Repeat again or try to increase max deviation');
        }

        $data[] = $endRate;

        return $data;
    }
}
