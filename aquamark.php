<?php

/**
 * Plugin Name:       AquaMark
 * Plugin URI:        https://github.com/williarin/aquamark
 * Description:       Add a custom watermark to your images in the WordPress media library with powerful controls and blending modes.
 * Version:           1.0.0
 * Author:            William Arin
 * Author URI:        https://github.com/williarin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aquamark
 */

use Williarin\AquaMark\Plugin;

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload_packages.php';

$plugin = new Plugin(__FILE__);
$plugin->run();
