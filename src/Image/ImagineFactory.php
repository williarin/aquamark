<?php

declare(strict_types=1);

namespace Plugin\Image;

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Image\ImagineInterface;
use Plugin\Admin\SettingsPage;
use Plugin\Settings\Settings;

final class ImagineFactory
{
    public static function create(): ImagineInterface
    {
        $options = get_option(SettingsPage::OPTION_NAME, []);
        $settings = new Settings($options);
        $driver = $settings->driver;

        $isImagickAvailable = extension_loaded('imagick') || class_exists('Imagick');
        $isGdAvailable = extension_loaded('gd') || function_exists('gd_info');

        if ($driver === 'auto') {
            return $isImagickAvailable ? new ImagickImagine() : new GdImagine();
        }

        if ($driver === 'imagick' && $isImagickAvailable) {
            return new ImagickImagine();
        }

        if ($driver === 'gd' && $isGdAvailable) {
            return new GdImagine();
        }

        // Fallback
        return $isImagickAvailable ? new ImagickImagine() : new GdImagine();
    }
}
