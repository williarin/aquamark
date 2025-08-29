<?php

declare(strict_types=1);

namespace Plugin\Tests\Unit\Blender;

use PHPUnit\Framework\TestCase;
use Plugin\Image\Blender\ScreenBlender;
use Imagine\Gd\Imagine;
use Imagine\Gd\Image as GdImage;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * @requires extension gd
 */
class ScreenBlenderTest extends TestCase
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

    public function testSupportsScreenMode(): void
    {
        $blender = new ScreenBlender();
        self::assertTrue($blender->supports('screen'));
        self::assertFalse($blender->supports('normal'));
    }

    public function testBlendColors(): void
    {
        // Test case 1: Black (0,0,0) * Black (0,0,0) = Black (0,0,0)
        $baseImage = $this->createGdImage(1, 1, [0, 0, 0]);
        $watermark = $this->createGdImage(1, 1, [0, 0, 0]);
        $blender = new ScreenBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [0, 0, 0]);

        // Test case 2: White (255,255,255) * Black (0,0,0) = White (255,255,255)
        $baseImage = $this->createGdImage(1, 1, [255, 255, 255]);
        $watermark = $this->createGdImage(1, 1, [0, 0, 0]);
        $blender = new ScreenBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [255, 255, 255]);

        // Test case 3: Mid-gray (128,128,128) * Mid-gray (128,128,128) = Lighter Gray (192,192,192)
        $baseImage = $this->createGdImage(1, 1, [128, 128, 128]);
        $watermark = $this->createGdImage(1, 1, [128, 128, 128]);
        $blender = new ScreenBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [192, 192, 192]); // Corrected expected value from 191 to 192

        // Test case 4: Red (255,0,0) * Blue (0,0,255) = Magenta (255,0,255)
        $baseImage = $this->createGdImage(1, 1, [255, 0, 0]);
        $watermark = $this->createGdImage(1, 1, [0, 0, 255]);
        $blender = new ScreenBlender();
        $blender->blend($baseImage, $watermark, new Point(0, 0));
        $this->assertPixelColor($baseImage, 0, 0, [255, 0, 255]);
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
