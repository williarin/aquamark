<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Image\ImagineInterface;
use Plugin\Admin\SettingsPage;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Plugin\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Settings}');

    $services->alias(ImagineInterface::class, 'imagine');
    $services->set('imagine', GdImagine::class);

    $services->get(SettingsPage::class)
        ->arg('$pluginFile', '%plugin.file%');

    $container->parameters()
        ->set('plugin.file', '');
};