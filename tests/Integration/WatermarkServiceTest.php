<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Tests\Integration;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Williarin\FreeWatermarks\Admin\SettingsPage;
use Williarin\FreeWatermarks\Image\Blender\BlenderInterface;
use Williarin\FreeWatermarks\Settings\Settings;
use Williarin\FreeWatermarks\Watermark\WatermarkService;

class WatermarkServiceTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();

        \WP_Mock::tearDown();
        \WP_Mock::setUp();

        $this->root = vfsStream::setup('tmp');
    }

    protected function tearDown(): void
    {
        \WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testApplyWatermarkReturnsMetadataWhenNoSettings(): void
    {
        \WP_Mock::userFunction('get_option')
            ->with(SettingsPage::OPTION_NAME, [])
            ->once()
            ->andReturn([]);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $blenderMock = $this->createMock(BlenderInterface::class);

        $service = new WatermarkService($imagineMock, $blenderMock);
        $metadata = ['file' => 'test.jpg'];
        $result = $service->applyWatermark($metadata, 1);

        self::assertSame($metadata, $result);
    }

    public function testApplyWatermarkReturnsMetadataWhenWatermarkNotFound(): void
    {
        $settings = new Settings([
            'watermarkImageId' => 99,
            'imageSizes' => ['thumbnail'],
        ]);

        \WP_Mock::userFunction('get_option')
            ->with(SettingsPage::OPTION_NAME, [])
            ->once()
            ->andReturn($settings->toArray());

        \WP_Mock::userFunction('wp_attachment_is_image')
            ->with(1)
            ->once()
            ->andReturn(true);

        \WP_Mock::userFunction('get_attached_file')
            ->with(99)
            ->once()
            ->andReturn(false);

        $imagineMock = $this->createMock(ImagineInterface::class);
        $blenderMock = $this->createMock(BlenderInterface::class);

        $service = new WatermarkService($imagineMock, $blenderMock);
        $metadata = ['file' => 'test.jpg'];
        $result = $service->applyWatermark($metadata, 1);

        self::assertSame($metadata, $result);
    }

    public function testApplyWatermarkProcessesImagesCorrectly(): void
    {
        vfsStream::create([
            'uploads' => [
                'watermark.png' => 'watermark_content',
                '2025' => [
                    '08' => [
                        'image-150x150.jpg' => 'thumb_content',
                        'image-300x300.jpg' => 'medium_content',
                    ]
                ]
            ]
        ], $this->root);

        $watermarkPath = vfsStream::url('tmp/uploads/watermark.png');
        $thumbPath = vfsStream::url('tmp/uploads/2025/08/image-150x150.jpg');
        $mediumPath = vfsStream::url('tmp/uploads/2025/08/image-300x300.jpg');

        $settings = new Settings([
            'watermarkImageId' => 100,
            'position' => 'bottom-right',
            'opacity' => 80,
            'blendMode' => 'opacity',
            'imageSizes' => ['thumbnail', 'medium'],
        ]);

        \WP_Mock::userFunction('get_option')
            ->with(SettingsPage::OPTION_NAME, [])
            ->once()
            ->andReturn($settings->toArray());

        \WP_Mock::userFunction('apply_filters')
            ->with('free_watermarks_settings', \WP_Mock\Functions::type(Settings::class), 1)
            ->andReturn($settings);

        \WP_Mock::userFunction('wp_attachment_is_image')
            ->with(1)
            ->once()
            ->andReturn(true);

        \WP_Mock::userFunction('get_attached_file')
            ->with(100)
            ->once()
            ->andReturn($watermarkPath);

        \WP_Mock::userFunction('wp_get_upload_dir')
            ->andReturn([
                'path' => vfsStream::url('tmp/uploads/2025/08'),
                'basedir' => vfsStream::url('tmp/uploads'),
            ]);

        $watermarkImageMock = $this->createMock(ImageInterface::class);
        $baseImageThumbnailMock = $this->createMock(ImageInterface::class);
        $baseImageMediumMock = $this->createMock(ImageInterface::class);
        $imagineMock = $this->createMock(ImagineInterface::class);
        $blenderMock = $this->createMock(BlenderInterface::class);

        $imagineMock->expects($this->exactly(3))
            ->method('open')
            ->willReturnMap([
                [$watermarkPath, $watermarkImageMock],
                [$thumbPath, $baseImageThumbnailMock],
                [$mediumPath, $baseImageMediumMock],
            ]);

        $watermarkImageMock->method('getSize')->willReturn(new Box(100, 100));
        $watermarkImageMock->method('copy')->willReturnSelf();
        $watermarkImageMock->method('resize')->willReturnSelf();
        $baseImageThumbnailMock->method('getSize')->willReturn(new Box(150, 150));
        $baseImageMediumMock->method('getSize')->willReturn(new Box(300, 300));

        \WP_Mock::userFunction('apply_filters')
            ->with('free_watermarks_watermark_image', $watermarkImageMock, $settings)
            ->andReturn($watermarkImageMock);

        $blenderMock->expects($this->exactly(2))->method('blend');

        $service = new WatermarkService($imagineMock, $blenderMock);

        $metadata = [
            'file' => '2025/08/image.jpg',
            'sizes' => [
                'thumbnail' => ['file' => 'image-150x150.jpg'],
                'medium' => ['file' => 'image-300x300.jpg'],
            ],
        ];

        $resultMetadata = $service->applyWatermark($metadata, 1);

        self::assertSame($metadata, $resultMetadata);
    }
}
