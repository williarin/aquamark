<?php

declare(strict_types=1);

namespace Plugin\Image\Blender;

use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class BlenderManager
{
    /** @var iterable<BlenderInterface> */
    private iterable $blenders;

    public function __construct(
        #[AutowireIterator(BlenderInterface::class)] iterable $blenders
    ) {
        $this->blenders = $blenders;
    }

    public function apply(string $mode, ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void
    {
        foreach ($this->blenders as $blender) {
            if ($blender->supports($mode)) {
                $blender->blend($image, $watermark, $start, $opacity);
                return;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unsupported blend mode: "%s"', $mode));
    }
}