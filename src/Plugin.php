<?php

declare(strict_types=1);

namespace Plugin;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Plugin\Admin\SettingsPage;
use Plugin\Watermark\RegenerateService;
use Plugin\Watermark\WatermarkService;

final class Plugin
{
    private ContainerBuilder $container;

    public function __construct(
        private readonly string $pluginFile
    ) {
    }

    public function run(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('plugin.file', $this->pluginFile);

        $loader = new PhpFileLoader($this->container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.php');

        $this->container->compile();

        $this->container->get(SettingsPage::class)->register();
        $this->container->get(WatermarkService::class)->register();
        $this->container->get(RegenerateService::class)->register();
    }
}
