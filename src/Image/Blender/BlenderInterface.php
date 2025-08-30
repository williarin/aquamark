<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Image\Blender;

use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface BlenderInterface
{
    public function supports(string $mode): bool;

    public function blend(string $mode, ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void;
}