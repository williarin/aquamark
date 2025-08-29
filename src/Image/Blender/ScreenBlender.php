<?php

declare(strict_types=1);

namespace Plugin\Image\Blender;

use Imagine\Gd\Image as GdImage;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

final class ScreenBlender implements BlenderInterface
{
    public function supports(string $mode): bool
    {
        return 'screen' === $mode;
    }

    public function blend(ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void
    {
        if ($image instanceof \Imagine\Imagick\Image && $watermark instanceof \Imagine\Imagick\Image) {
            $imagick = $image->getImagick();
            $watermarkImagick = $watermark->getImagick();

            $watermarkImagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

            if ($opacity < 100) {
                $watermarkImagick->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity / 100, \Imagick::CHANNEL_ALPHA);
            }

            $imagick->compositeImage($watermarkImagick, \Imagick::COMPOSITE_SCREEN, $start->getX(), $start->getY());
        } elseif ($image instanceof GdImage && $watermark instanceof GdImage) {
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

                    // Screen formula
                    $newRed = 255 - (int) (((255 - $baseRed) * (255 - $watermarkRed)) / 255);
                    $newGreen = 255 - (int) (((255 - $baseGreen) * (255 - $watermarkGreen)) / 255);
                    $newBlue = 255 - (int) (((255 - $baseBlue) * (255 - $watermarkBlue)) / 255);

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
        } else {
            throw new \LogicException('Unsupported image driver combination.');
        }
    }
}
