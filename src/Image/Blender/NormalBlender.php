<?php

declare(strict_types=1);

namespace Plugin\Image\Blender;

use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

final class NormalBlender implements BlenderInterface
{
    public function supports(string $mode): bool
    {
        return 'normal' === $mode;
    }

    public function blend(ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void
    {
        $image->paste($watermark, $start, $opacity);
    }
}
