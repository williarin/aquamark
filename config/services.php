<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Imagine\Image\ImagineInterface;
use Williarin\FreeWatermarks\Admin\SettingsPage;
use Williarin\FreeWatermarks\Watermark\RegenerateService;
use Williarin\FreeWatermarks\Watermark\RemoveService;
use Williarin\FreeWatermarks\Watermark\WatermarkService;
use Williarin\FreeWatermarks\Image\ImagineFactory;
use Williarin\FreeWatermarks\Image\Blender\PixelBlender\OpacityPixelBlender;
use Williarin\FreeWatermarks\Image\Blender\PixelBlender\MultiplyPixelBlender;
use Williarin\FreeWatermarks\Image\Blender\PixelBlender\ScreenPixelBlender;
use Williarin\FreeWatermarks\Image\Blender\PixelBlender\OverlayPixelBlender;
use Williarin\FreeWatermarks\Image\Blender\PixelBlender\PixelBlenderInterface;
use Williarin\FreeWatermarks\BlendModeEnum;
use Williarin\FreeWatermarks\Image\Blender\ImageBlender;
use Williarin\FreeWatermarks\Image\Blender\BlenderInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Williarin\\FreeWatermarks\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Settings,Image/Blender}');

    // Add ImageBlender
    $services->set(ImageBlender::class)
        ->arg('$pixelBlenders', tagged_locator('plugin.pixel_blender', 'key'));

    $services->alias(BlenderInterface::class, ImageBlender::class);

    // Add PixelBlenders
    $services->set(OpacityPixelBlender::class)->tag('plugin.pixel_blender', ['key' => BlendModeEnum::Opacity->value]);
    $services->set(MultiplyPixelBlender::class)->tag('plugin.pixel_blender', ['key' => BlendModeEnum::Multiply->value]);
    $services->set(ScreenPixelBlender::class)->tag('plugin.pixel_blender', ['key' => BlendModeEnum::Screen->value]);
    $services->set(OverlayPixelBlender::class)->tag('plugin.pixel_blender', ['key' => BlendModeEnum::Overlay->value]);

    $services->alias(ImagineInterface::class, 'imagine');
    $services->set('imagine', ImagineInterface::class)
        ->factory([ImagineFactory::class, 'create']);

    $services->get(SettingsPage::class)
        ->arg('$pluginFile', '%plugin.file%')
        ->arg('$watermarkService', service(WatermarkService::class))
        ->public();

    $services->get(WatermarkService::class)->public();
    $services->get(RegenerateService::class)->public();
    $services->get(RemoveService::class)->public();
};
