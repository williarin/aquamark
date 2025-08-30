<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Image\Blender\PixelBlender;

final class OverlayPixelBlender extends AbstractPixelBlender
{
    public function blend(int $basePixel, int $topPixel): int
    {
        if ($basePixel < 128) {
            $result = (2 * $basePixel * $topPixel) / 255;
        } else {
            $result = 255 - (2 * (255 - $basePixel) * (255 - $topPixel)) / 255;
        }
        return (int) floor($result);
    }
}
