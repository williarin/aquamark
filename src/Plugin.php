<?php

declare(strict_types=1);

namespace Williarin\AquaMark;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Williarin\AquaMark\Admin\SettingsPage;
use Williarin\AquaMark\Watermark\RegenerateService;
use Williarin\AquaMark\Watermark\RemoveService;
use Williarin\AquaMark\Watermark\WatermarkService;

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
        $this->container->get(RemoveService::class)->register();

        add_filter('plugin_action_links_' . plugin_basename($this->pluginFile), [$this, 'addPluginActionLinks']);
    }

    public function addPluginActionLinks(array $links): array
    {
        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=aquamark'),
            __('Settings', 'aquamark')
        );

        array_unshift($links, $settingsLink);

        return $links;
    }
}
