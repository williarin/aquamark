<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Image\ImagineInterface;
use Plugin\Admin\SettingsPage;
use Plugin\Watermark\RegenerateService;
use Plugin\Watermark\WatermarkService;
use Plugin\Image\ImagineFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Plugin\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Settings}');

    $services->alias(ImagineInterface::class, 'imagine');
    $services->set('imagine', ImagineInterface::class)
        ->factory([ImagineFactory::class, 'create']);

    $services->get(SettingsPage::class)
        ->arg('$pluginFile', '%plugin.file%')
        ->public();

    $services->get(WatermarkService::class)->public();
    $services->get(RegenerateService::class)->public();
};
