<?php

declare(strict_types=1);

namespace Plugin\Tests\Unit\Blender;

use PHPUnit\Framework\TestCase;
use Plugin\Image\Blender\NormalBlender;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class NormalBlenderTest extends TestCase
{
    public function testSupportsNormalMode(): void
    {
        $blender = new NormalBlender();
        self::assertTrue($blender->supports('normal'));
        self::assertFalse($blender->supports('multiply'));
    }

    public function testBlendCallsPasteOnImage(): void
    {
        $imageMock = $this->createMock(ImageInterface::class);
        $watermarkMock = $this->createMock(ImageInterface::class);
        $point = new Point(0, 0); // Use a real Point object

        $imageMock->expects($this->once())
            ->method('paste')
            ->with($watermarkMock, $point);

        $blender = new NormalBlender();
        $blender->blend($imageMock, $watermarkMock, $point);
    }
}