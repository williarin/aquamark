<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Imagine\Image\ImagineInterface;
use Williarin\AquaMark\Admin\SettingsPage;
use Williarin\AquaMark\Watermark\RegenerateService;
use Williarin\AquaMark\Watermark\RemoveService;
use Williarin\AquaMark\Watermark\WatermarkService;
use Williarin\AquaMark\Image\ImagineFactory;
use Williarin\AquaMark\Image\Blender\PixelBlender\OpacityPixelBlender;
use Williarin\AquaMark\Image\Blender\PixelBlender\MultiplyPixelBlender;
use Williarin\AquaMark\Image\Blender\PixelBlender\ScreenPixelBlender;
use Williarin\AquaMark\Image\Blender\PixelBlender\OverlayPixelBlender;
use Williarin\AquaMark\Image\Blender\PixelBlender\PixelBlenderInterface;
use Williarin\AquaMark\BlendModeEnum;
use Williarin\AquaMark\Image\Blender\ImageBlender;
use Williarin\AquaMark\Image\Blender\BlenderInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Williarin\\AquaMark\\', '../src/*')
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
