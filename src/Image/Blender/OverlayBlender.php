<?php

declare(strict_types=1);

namespace Plugin\Image\Blender;

use Imagine\Gd\Image as GdImage;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

final class OverlayBlender implements BlenderInterface
{
    public function supports(string $mode): bool
    {
        return 'overlay' === $mode;
    }

    public function blend(ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void
    {
        if (!$image instanceof GdImage || !$watermark instanceof GdImage) {
            throw new \LogicException(sprintf(
                'The %s currently only supports the GD driver.',
                self::class
            ));
        }

        $baseResource = $image->getGdResource();
        $watermarkResource = $watermark->getGdResource();

        $width = $watermark->getSize()->getWidth();
        $height = $watermark->getSize()->getHeight();
        $startX = $start->getX();
        $startY = $start->getY();

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $baseRgb = imagecolorat($baseResource, $startX + $x, $startY + $y);
                $baseRed = ($baseRgb >> 16) & 0xFF;
                $baseGreen = ($baseRgb >> 8) & 0xFF;
                $baseBlue = $baseRgb & 0xFF;

                $watermarkRgb = imagecolorat($watermarkResource, $x, $y);
                $watermarkRed = ($watermarkRgb >> 16) & 0xFF;
                $watermarkGreen = ($watermarkRgb >> 8) & 0xFF;
                $watermarkBlue = $watermarkRgb & 0xFF;
                $watermarkAlpha = ($watermarkRgb >> 24) & 0x7F;

                // Overlay formula
                $newRed = $this->getOverlayValue($baseRed, $watermarkRed);
                $newGreen = $this->getOverlayValue($baseGreen, $watermarkGreen);
                $newBlue = $this->getOverlayValue($baseBlue, $watermarkBlue);

                // Alpha blending with opacity
                $alpha = $watermarkAlpha / 127;
                // Apply the opacity setting
                $alpha = $alpha * ($opacity / 100);
                $finalRed = (int) ($newRed * (1 - $alpha) + $baseRed * $alpha);
                $finalGreen = (int) ($newGreen * (1 - $alpha) + $baseGreen * $alpha);
                $finalBlue = (int) ($newBlue * (1 - $alpha) + $baseBlue * $alpha);

                $newColor = imagecolorallocate($baseResource, $finalRed, $finalGreen, $finalBlue);
                imagesetpixel($baseResource, $startX + $x, $startY + $y, $newColor);
            }
        }
    }

    private function getOverlayValue(int $base, int $watermark): int
    {
        return ($base < 128)
            ? (int) ((2 * $base * $watermark) / 255)
            : 255 - (int) ((2 * (255 - $base) * (255 - $watermark)) / 255);
    }
}
