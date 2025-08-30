<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Image\Blender;

use Imagine\Gd\Image as GdImage;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Psr\Container\ContainerInterface;
use Williarin\FreeWatermarks\Image\Blender\PixelBlender\PixelBlenderInterface;
use Williarin\FreeWatermarks\BlendModeEnum;

final class ImageBlender implements BlenderInterface
{
    private array $imagickCompositeModes = [];

    public function __construct(
        private readonly ContainerInterface $pixelBlenders,
    ) {
        if (class_exists('Imagick')) {
            $this->imagickCompositeModes = [
                BlendModeEnum::Opacity->value => \Imagick::COMPOSITE_OVER,
                BlendModeEnum::Multiply->value => \Imagick::COMPOSITE_MULTIPLY,
                BlendModeEnum::Screen->value => \Imagick::COMPOSITE_SCREEN,
                BlendModeEnum::Overlay->value => \Imagick::COMPOSITE_OVERLAY,
            ];
        }
    }

    public function supports(string $mode): bool
    {
        return $this->pixelBlenders->has($mode);
    }

    public function blend(string $mode, ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity = 100): void
    {
        if ($image instanceof \Imagine\Imagick\Image && $watermark instanceof \Imagine\Imagick\Image) {
            $this->blendImagick($mode, $image, $watermark, $start, $opacity);
        } elseif ($image instanceof GdImage && $watermark instanceof GdImage) {
            $this->blendGd($mode, $image, $watermark, $start, $opacity);
        } else {
            throw new \LogicException('Unsupported image driver combination.');
        }
    }

    private function blendImagick(string $mode, ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity): void
    {
        /** @var \Imagine\Imagick\Image $image */
        /** @var \Imagine\Imagick\Image $watermark */
        $imagick = $image->getImagick();
        $watermarkImagick = $watermark->getImagick();

        $watermarkImagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

        if ($opacity < 100) {
            $watermarkImagick->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity / 100, \Imagick::CHANNEL_ALPHA);
        }

        $compositeMode = $this->imagickCompositeModes[$mode] ?? \Imagick::COMPOSITE_OVER;
        $imagick->compositeImage($watermarkImagick, $compositeMode, $start->getX(), $start->getY());
    }

    private function blendGd(string $mode, ImageInterface $image, ImageInterface $watermark, Point $start, int $opacity): void
    {
        /** @var GdImage $image */
        /** @var GdImage $watermark */
        $baseResource = $image->getGdResource();
        $watermarkResource = $watermark->getGdResource();

        $width = $watermark->getSize()->getWidth();
        $height = $watermark->getSize()->getHeight();
        $startX = $start->getX();
        $startY = $start->getY();

        imagealphablending($baseResource, true);
        imagesavealpha($baseResource, true);

        $opacityRatio = $opacity / 100;

        /** @var PixelBlenderInterface $pixelBlender */
        $pixelBlender = $this->pixelBlenders->get($mode);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $watermarkRgb = imagecolorsforindex($watermarkResource, imagecolorat($watermarkResource, $x, $y));
                $baseRgb = imagecolorsforindex($baseResource, imagecolorat($baseResource, $startX + $x, $startY + $y));

                $watermarkAlpha = 1 - ($watermarkRgb['alpha'] / 127);
                $opacityAlpha = $watermarkAlpha * $opacityRatio;

                $newRed = $pixelBlender->blend($baseRgb['red'], $watermarkRgb['red']);
                $newGreen = $pixelBlender->blend($baseRgb['green'], $watermarkRgb['green']);
                $newBlue = $pixelBlender->blend($baseRgb['blue'], $watermarkRgb['blue']);

                $finalRed = (int) (($newRed * $opacityAlpha) + ($baseRgb['red'] * (1 - $opacityAlpha)));
                $finalGreen = (int) (($newGreen * $opacityAlpha) + ($baseRgb['green'] * (1 - $opacityAlpha)));
                $finalBlue = (int) (($newBlue * $opacityAlpha) + ($baseRgb['blue'] * (1 - $opacityAlpha)));

                $newColor = imagecolorallocatealpha($baseResource, $finalRed, $finalGreen, $finalBlue, $baseRgb['alpha']);
                imagesetpixel($baseResource, $startX + $x, $startY + $y, $newColor);
            }
        }
    }
}
