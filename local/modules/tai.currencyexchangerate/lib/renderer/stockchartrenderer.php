<?php

namespace Tai\CurrencyExchangeRate\Renderer;
use Exception;
use Imagick;
use ImagickDraw;
use ImagickPixel;

class StockChartRenderer
{
    private ?string $filePath;
    private int $width = 400;
    private int $height = 200;
    private array $data = [];

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $path): self
    {
        $this->filePath = $path;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function render(): self
    {
        $filePath = $this->getFilePath();

        if (empty($filePath)) {
            throw new Exception('File path is not set for render');
        }

        if (count($this->getData()) < 2) {
            throw new Exception('Data must contain at least two points');
        }

        $width = $this->getWidth();
        $height = $this->getHeight();
        $lineWidth = 4;
        $padding = 10;

        $topDataPoint = max($this->getData());
        $bottomDataPoint = min($this->getData());
        $chartDataHeight = $topDataPoint - $bottomDataPoint;
        $chartXYHeight = $height - 2*$padding;
        $scale = $chartXYHeight / $chartDataHeight;

        $yPoints = [];

        foreach ($this->getData() as $dataPoint) {
            $yPoints[] = $padding + ($topDataPoint - $dataPoint) * $scale;
        }

        $imgck = new Imagick();
        $background = new ImagickPixel('white');

        $imgck->newImage($width, $height, $background);
        $imgck->setImageFormat('png');

        $chart = new ImagickDraw();

        $chart->setStrokeAntialias(true);

        $chart->setStrokeColor(new ImagickPixel('#3169b5'));
        $chart->setStrokeWidth($lineWidth);

        if (count($yPoints) > 1) {
            $x = $padding;
            $xStep = ($width - 2*$padding) / (count($yPoints) - 1);

            for ($i = 0; $i < count($yPoints) - 1; $i++) {
                $chart->line($x, $yPoints[$i], $x + $xStep, $yPoints[$i + 1]);

                $x += $xStep;
            }
        }

        $imgck->drawImage($chart);
        $imgck->writeImage($filePath);

        return $this;
    }
}
