<?php

declare(strict_types=1);

namespace Williarin\AquaMark\Image\Blender\PixelBlender;

final class MultiplyPixelBlender extends AbstractPixelBlender
{
    public function blend(int $basePixel, int $topPixel): int
    {
        $result = ($basePixel * $topPixel) / 255;
        return (int) floor($result);
    }
}
