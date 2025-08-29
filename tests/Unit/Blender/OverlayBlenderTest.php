<?php

declare(strict_types=1);

namespace Plugin\Tests\Unit\Blender;

use PHPUnit\Framework\TestCase;
use Plugin\Image\Blender\OverlayBlender;
use Imagine\Gd\Imagine;
use Imagine\Gd\Image as GdImage;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * @requires extension gd
 */
class OverlayBlenderTest extends TestCase
{
    private Imagine $imagine;

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('GD extension is not available.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->imagine = new Imagine();
    }

    public function testSupportsOverlayMode(): void
    {
        $blender = new OverlayBlender();
        self::assertTrue($blender->supports('overlay'));
        self::assertFalse($blender->supports('normal'));
    }

    public function testBlendColors(): void
    {
        // Test case 1 (Bottom < 128, Multiply branch): Black (0,0,0) * Mid-gray (128,128,128) = Black (0,0,0)
        $baseImage = $this->createGdImage(1, 1, [0, 0, 0]);
        $watermark = $this->createGdImage(1, 1, [128, 128, 128]);
        $blender = new OverlayBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [0, 0, 0]);

        // Test case 2 (Bottom < 128, Multiply branch): Dark Gray (64,64,64) * Dark Gray (64,64,64) = Very Dark Gray (32,32,32)
        $baseImage = $this->createGdImage(1, 1, [64, 64, 64]);
        $watermark = $this->createGdImage(1, 1, [64, 64, 64]);
        $blender = new OverlayBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [32, 32, 32]);

        // Test case 3 (Bottom >= 128, Screen branch): White (255,255,255) * Mid-gray (128,128,128) = White (255,255,255)
        $baseImage = $this->createGdImage(1, 1, [255, 255, 255]);
        $watermark = $this->createGdImage(1, 1, [128, 128, 128]);
        $blender = new OverlayBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [255, 255, 255]);

        // Test case 4 (Bottom >= 128, Screen branch): Light Gray (192,192,192) * Light Gray (192,192,192) = Very Light Gray (224,224,224)
        $baseImage = $this->createGdImage(1, 1, [192, 192, 192]);
        $watermark = $this->createGdImage(1, 1, [192, 192, 192]);
        $blender = new OverlayBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [224, 224, 224]); // Corrected expected value from 223 to 224
    }

    private function createGdImage(int $width, int $height, array $rgb): GdImage
    {
        $image = $this->imagine->create(new Box($width, $height));
        $resource = $image->getGdResource();
        $color = imagecolorallocate($resource, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($resource, 0, 0, $color);
        return $image;
    }

    private function assertPixelColor(GdImage $image, int $x, int $y, array $expectedRgb): void
    {
        $resource = $image->getGdResource();
        $color = imagecolorat($resource, $x, $y);
        $actualR = ($color >> 16) & 0xFF;
        $actualG = ($color >> 8) & 0xFF;
        $actualB = $color & 0xFF;

        self::assertEquals($expectedRgb[0], $actualR, "Red component mismatch at ($x, $y)");
        self::assertEquals($expectedRgb[1], $actualG, "Green component mismatch at ($x, $y)");
        self::assertEquals($expectedRgb[2], $actualB, "Blue component mismatch at ($x, $y)");
    }
}
