<?php

declare(strict_types=1);

namespace Plugin\Image\Blender;

use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface BlenderInterface
{
    public function supports(string $mode): bool;

    public function blend(ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void;
}