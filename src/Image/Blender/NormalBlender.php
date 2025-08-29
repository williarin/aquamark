<?php

declare(strict_types=1);

namespace Plugin\Image\Blender;

use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

final class NormalBlender implements BlenderInterface
{
    public function supports(string $mode): bool
    {
        return 'opacity' === $mode;
    }

    public function blend(ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void
    {
        if ($image instanceof \Imagine\Imagick\Image && $watermark instanceof \Imagine\Imagick\Image) {
            $imagick = $image->getImagick();
            $watermarkImagick = $watermark->getImagick();

            // Ensure alpha channel is active before manipulating it
            $watermarkImagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

            // Set opacity
            if ($opacity < 100) {
                $watermarkImagick->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity / 100, \Imagick::CHANNEL_ALPHA);
            }

            $imagick->compositeImage($watermarkImagick, \Imagick::COMPOSITE_OVER, $start->getX(), $start->getY());
        } else {
            $baseResource = $image->getGdResource();
            $watermarkResource = $watermark->getGdResource();

            $width = $watermark->getSize()->getWidth();
            $height = $watermark->getSize()->getHeight();
            $startX = $start->getX();
            $startY = $start->getY();

            $opacityRatio = $opacity / 100;

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $watermarkRgb = imagecolorsforindex($watermarkResource, imagecolorat($watermarkResource, $x, $y));
                    $baseRgb = imagecolorsforindex($baseResource, imagecolorat($baseResource, $startX + $x, $startY + $y));

                    $watermarkAlpha = $watermarkRgb['alpha'];

                    $combinedAlpha = 1 - ((1 - ($watermarkAlpha / 127)) * $opacityRatio);

                    if ($combinedAlpha >= 1) {
                        continue;
                    }

                    $newRed = (int) ($baseRgb['red'] * $combinedAlpha + $watermarkRgb['red'] * (1 - $combinedAlpha));
                    $newGreen = (int) ($baseRgb['green'] * $combinedAlpha + $watermarkRgb['green'] * (1 - $combinedAlpha));
                    $newBlue = (int) ($baseRgb['blue'] * $combinedAlpha + $watermarkRgb['blue'] * (1 - $combinedAlpha));

                    $newAlpha = $baseRgb['alpha'];

                    $newColor = imagecolorallocatealpha($baseResource, $newRed, $newGreen, $newBlue, $newAlpha);
                    imagesetpixel($baseResource, $startX + $x, $startY + $y, $newColor);
                }
            }
        }
    }
}
