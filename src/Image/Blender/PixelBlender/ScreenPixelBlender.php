<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Image\Blender\PixelBlender;

final class ScreenPixelBlender extends AbstractPixelBlender
{
    public function blend(int $basePixel, int $topPixel): int
    {
        $result = 255 - ((255 - $basePixel) * (255 - $topPixel)) / 255;
        return (int) floor($result);
    }
}
