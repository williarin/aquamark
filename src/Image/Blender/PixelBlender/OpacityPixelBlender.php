<?php

declare(strict_types=1);

namespace Williarin\AquaMark\Image\Blender\PixelBlender;

final class OpacityPixelBlender extends AbstractPixelBlender
{
    public function blend(int $basePixel, int $topPixel): int
    {
        return $topPixel; // The actual opacity will be applied in ImageBlender
    }
}
