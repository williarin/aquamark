<?php

declare(strict_types=1);

namespace Williarin\AquaMark\Image\Blender\PixelBlender;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('plugin.pixel_blender')]
interface PixelBlenderInterface
{
    public function blend(int $basePixel, int $topPixel): int;
}
