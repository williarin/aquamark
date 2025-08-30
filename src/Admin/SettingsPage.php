<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Admin;

use Williarin\FreeWatermarks\Settings\Settings;
use Williarin\FreeWatermarks\BlendModeEnum;

final class SettingsPage
{
    public const OPTION_NAME = 'free_watermarks_settings';
    private array $options = [];

    public function __construct(
        private readonly string $pluginFile
    ) {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addOptionsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function enqueueScripts(string $hook): void
    {
        if ('settings_page_free-watermarks' !== $hook) {
            return;
        }

        $scriptUrl = plugin_dir_url($this->pluginFile) . 'assets/js/admin.js';
        
        wp_enqueue_script('free-watermarks-admin', $scriptUrl, ['jquery', 'media-upload', 'thickbox'], '1.0.0', true);
        wp_enqueue_media();
    }

    public function addOptionsPage(): void
    {
        add_options_page(
            __('Free Watermarks', 'free-watermarks'),
            __('Free Watermarks', 'free-watermarks'),
            'manage_options',
            'free-watermarks',
            [$this, 'renderPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting('free-watermarks', self::OPTION_NAME, [
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => (new Settings([]))->toArray(),
        ]);

        add_settings_section(
            'free_watermarks_general',
            __('Watermark Settings', 'free-watermarks'),
            [$this, 'renderSectionHeader'],
            'free-watermarks'
        );

        add_settings_field('watermarkImageId', __('Watermark Image', 'free-watermarks'), [$this, 'renderWatermarkImageField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('position', __('Position', 'free-watermarks'), [$this, 'renderPositionField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('offset', __('Offset', 'free-watermarks'), [$this, 'renderOffsetField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('size', __('Size', 'free-watermarks'), [$this, 'renderSizeField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('opacity', __('Opacity', 'free-watermarks'), [$this, 'renderOpacityField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('blendMode', __('Blend Mode', 'free-watermarks'), [$this, 'renderBlendModeField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('imageSizes', __('Apply to Image Sizes', 'free-watermarks'), [$this, 'renderImageSizesField'], 'free-watermarks', 'free_watermarks_general');
        add_settings_field('driver', __('Image Processing Driver', 'free-watermarks'), [$this, 'renderDriverField'], 'free-watermarks', 'free_watermarks_general');
    }

    public function sanitize(array $input): array
    {
        $input['watermarkImageId'] = isset($input['watermarkImageId']) ? (int) $input['watermarkImageId'] : 0;
        $input['offsetX'] = isset($input['offsetX']) ? (int) $input['offsetX'] : 0;
        $input['offsetY'] = isset($input['offsetY']) ? (int) $input['offsetY'] : 0;
        $input['width'] = isset($input['width']) ? (int) $input['width'] : 0;
        $input['height'] = isset($input['height']) ? (int) $input['height'] : 0;
        $input['opacity'] = isset($input['opacity']) ? (int) $input['opacity'] : 0;

        $settings = new Settings($input);
        return $settings->toArray();
    }

    public function renderSectionHeader(): void
    {
        echo '<p>' . __('Configure the appearance and placement of the watermark.', 'free-watermarks') . '</p>';
    }

    public function renderWatermarkImageField(): void
    {
        $imageId = $this->options['watermarkImageId'] ?? 0;
        $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'medium') : '';
        ?>
        <div class="free-watermarks-image-preview" style="margin-bottom: 10px;">
            <?php if ($imageUrl): ?>
                <img src="<?php echo esc_url($imageUrl); ?>" style="max-width: 200px; max-height: 200px;">
            <?php endif; ?>
        </div>
        <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[watermarkImageId]" value="<?php echo esc_attr($imageId); ?>">
        <button type="button" class="button button-secondary" id="free-watermarks-upload-button">
            <?php _e('Select Image', 'free-watermarks'); ?>
        </button>
        <button type="button" class="button button-secondary" id="free-watermarks-remove-button" style="display: <?php echo $imageId ? 'inline-block' : 'none'; ?>;">
            <?php _e('Remove Image', 'free-watermarks'); ?>
        </button>
        <?php
    }

    public function renderPositionField(): void
    {
        $position = $this->options['position'] ?? 'bottom-right';
        $positions = [
            'top-left' => __('Top Left', 'free-watermarks'), 'top-center' => __('Top Center', 'free-watermarks'), 'top-right' => __('Top Right', 'free-watermarks'),
            'middle-left' => __('Middle Left', 'free-watermarks'), 'middle-center' => __('Middle Center', 'free-watermarks'), 'middle-right' => __('Middle Right', 'free-watermarks'),
            'bottom-left' => __('Bottom Left', 'free-watermarks'), 'bottom-center' => __('Bottom Center', 'free-watermarks'), 'bottom-right' => __('Bottom Right', 'free-watermarks'),
        ];
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[position]">
            <?php foreach ($positions as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $position); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function renderOffsetField(): void
    {
        $offsetX = $this->options['offsetX'] ?? 10;
        $offsetY = $this->options['offsetY'] ?? 10;
        $unit = $this->options['offsetUnit'] ?? 'px';
        ?>
        <input type="number" name="<?php echo esc_attr(self::OPTION_NAME); ?>[offsetX]" value="<?php echo esc_attr($offsetX); ?>" style="width: 80px;">
        <input type="number" name="<?php echo esc_attr(self::OPTION_NAME); ?>[offsetY]" value="<?php echo esc_attr($offsetY); ?>" style="width: 80px;">
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[offsetUnit]">
            <option value="px" <?php selected('px', $unit); ?>>px</option>
            <option value="%" <?php selected('%', $unit); ?>>%</option>
        </select>
        <p class="description"><?php _e('X and Y offset from the chosen position.', 'free-watermarks'); ?></p>
        <?php
    }

    public function renderSizeField(): void
    {
        $width = $this->options['width'] ?? 150;
        $height = $this->options['height'] ?? 0;
        $unit = $this->options['sizeUnit'] ?? 'px';
        ?>
        <input type="number" name="<?php echo esc_attr(self::OPTION_NAME); ?>[width]" value="<?php echo esc_attr($width); ?>" style="width: 80px;">
        <input type="number" name="<?php echo esc_attr(self::OPTION_NAME); ?>[height]" value="<?php echo esc_attr($height); ?>" style="width: 80px;">
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[sizeUnit]">
            <option value="px" <?php selected('px', $unit); ?>>px</option>
            <option value="%" <?php selected('%', $unit); ?>>%</option>
        </select>
        <p class="description"><?php _e('Width and Height of the watermark. Set height to 0 for auto-scaling.', 'free-watermarks'); ?></p>
        <?php
    }

    public function renderOpacityField(): void
    {
        $opacity = $this->options['opacity'] ?? 80;
        ?>
        <input type="number" name="<?php echo esc_attr(self::OPTION_NAME); ?>[opacity]" value="<?php echo esc_attr($opacity); ?>" min="0" max="100" step="1">
        <p class="description"><?php _e('Opacity in percent (0-100).', 'free-watermarks'); ?></p>
        <?php
    }

    public function renderBlendModeField(): void
    {
        $blendMode = $this->options['blendMode'] ?? BlendModeEnum::Opacity->value;
        $modes = BlendModeEnum::values();
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[blendMode]">
            <?php foreach ($modes as $mode): ?>
                <option value="<?php echo esc_attr($mode); ?>" <?php selected($mode, $blendMode); ?>><?php echo esc_html(ucfirst($mode)); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php _e('How the watermark blends with the image.', 'free-watermarks'); ?></p>
        <?php
    }

    public function renderImageSizesField(): void
    {
        global $_wp_additional_image_sizes;

        $appliedSizes = $this->options['imageSizes'] ?? [];
        $availableSizes = get_intermediate_image_sizes();
        $availableSizes[] = 'full';
        ?>
        <fieldset>
            <?php foreach ($availableSizes as $size):
                $dimensions = '';
                if ($size !== 'full') {
                    $width = 0;
                    $height = 0;

                    if (in_array($size, ['thumbnail', 'medium', 'medium_large', 'large'])) {
                        $width = (int) get_option($size . '_size_w');
                        $height = (int) get_option($size . '_size_h');
                    } elseif (isset($_wp_additional_image_sizes[$size])) {
                        $width = $_wp_additional_image_sizes[$size]['width'];
                        $height = $_wp_additional_image_sizes[$size]['height'];
                    }

                    if ($width > 0 || $height > 0) {
                        $dimensions = sprintf(' (%dx%d)', $width, $height);
                    }
                }
                ?>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[imageSizes][]" value="<?php echo esc_attr($size); ?>" <?php checked(in_array($size, $appliedSizes, true)); ?>>
                    <?php echo esc_html($size); ?><?php echo $dimensions; ?>
                    <?php if ('full' === $size): ?>
                        <em style="color: red;">(<?php _e('Warning: Applying to full size is destructive and cannot be easily undone.', 'free-watermarks'); ?>)</em>
                    <?php endif; ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    public function renderDriverField(): void
    {
        $isImagickAvailable = extension_loaded('imagick') || class_exists('Imagick');
        $isGdAvailable = extension_loaded('gd') || function_exists('gd_info');
        $currentDriver = $this->options['driver'] ?? 'auto';

        if ($isImagickAvailable && $isGdAvailable) {
            ?>
            <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[driver]">
                <option value="auto" <?php selected('auto', $currentDriver); ?>><?php _e('Auto (Recommended)', 'free-watermarks'); ?></option>
                <option value="imagick" <?php selected('imagick', $currentDriver); ?>><?php _e('Imagick (High Quality)', 'free-watermarks'); ?></option>
                <option value="gd" <?php selected('gd', $currentDriver); ?>><?php _e('GD (Compatibility)', 'free-watermarks'); ?></option>
            </select>
            <p class="description"><?php _e('Choose the image processing library. Auto will use Imagick if available.', 'free-watermarks'); ?></p>
            <?php
        } elseif ($isImagickAvailable) {
            ?>
            <p><strong><?php _e('Imagick', 'free-watermarks'); ?></strong></p>
            <p class="description"><?php _e('Your server is using the Imagick library for high-quality image processing.', 'free-watermarks'); ?></p>
            <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[driver]" value="auto">
            <?php
        } elseif ($isGdAvailable) {
            ?>
            <p><strong><?php _e('GD', 'free-watermarks'); ?></strong></p>
            <p class="description"><?php _e('Your server is using the GD library. For higher quality, consider installing the Imagick extension.', 'free-watermarks'); ?></p>
            <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[driver]" value="auto">
            <?php
        } else {
            ?>
            <p style="color: red;"><strong><?php _e('No compatible image processing library found!', 'free-watermarks'); ?></strong></p>
            <p class="description"><?php _e('This plugin requires either the GD or Imagick PHP extension to be installed.', 'free-watermarks'); ?></p>
            <?php
        }
    }

    public function renderPage(): void
    {
        $this->options = (array) get_option(self::OPTION_NAME, []);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('free-watermarks');
                do_settings_sections('free-watermarks');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
