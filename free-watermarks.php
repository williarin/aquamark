<?php

/**
 * Plugin Name:       Free Watermarks
 * Plugin URI:        https://github.com/williarin/free-watermarks
 * Description:       The best free watermarking plugin ever created. Add watermarks to your images in the WordPress media library.
 * Version:           1.0.0
 * Author:            William Arin
 * Author URI:        https://github.com/williarin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       free-watermarks
 * Domain Path:       /languages
 */

use Plugin\Plugin;

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload_packages.php';

$plugin = new Plugin(__FILE__);
$plugin->run();
