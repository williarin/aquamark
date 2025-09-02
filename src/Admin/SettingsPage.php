<?php

declare(strict_types=1);

namespace Williarin\AquaMark\Admin;

use Williarin\AquaMark\Settings\Settings;
use Williarin\AquaMark\BlendModeEnum;
use Williarin\AquaMark\Watermark\WatermarkService;

final class SettingsPage
{
    public const OPTION_NAME = 'aquamark_settings';
    private array $options = [];

    public function __construct(
        private readonly string $pluginFile,
        private readonly WatermarkService $watermarkService
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
        if ('settings_page_aquamark' !== $hook) {
            return;
        }

        $scriptUrl = plugin_dir_url($this->pluginFile) . 'assets/js/admin.js';
        
        wp_enqueue_script('aquamark-admin', $scriptUrl, ['jquery', 'media-upload', 'thickbox'], '1.0.0', true);
        wp_enqueue_media();
    }

    public function addOptionsPage(): void
    {
        add_options_page(
            __('AquaMark', 'aquamark'),
            __('AquaMark', 'aquamark'),
            'manage_options',
            'aquamark',
            [$this, 'renderPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting('aquamark', self::OPTION_NAME, [
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => (new Settings([]))->toArray(),
        ]);

        add_settings_section(
            'aquamark_general',
            __('Watermark Settings', 'aquamark'),
            [$this, 'renderSectionHeader'],
            'aquamark'
        );

        add_settings_field('watermarkImageId', __('Watermark Image', 'aquamark'), [$this, 'renderWatermarkImageField'], 'aquamark', 'aquamark_general');
        add_settings_field('position', __('Position', 'aquamark'), [$this, 'renderPositionField'], 'aquamark', 'aquamark_general');
        add_settings_field('offset', __('Offset', 'aquamark'), [$this, 'renderOffsetField'], 'aquamark', 'aquamark_general');
        add_settings_field('size', __('Size', 'aquamark'), [$this, 'renderSizeField'], 'aquamark', 'aquamark_general');
        add_settings_field('opacity', __('Opacity', 'aquamark'), [$this, 'renderOpacityField'], 'aquamark', 'aquamark_general');
        add_settings_field('blendMode', __('Blend Mode', 'aquamark'), [$this, 'renderBlendModeField'], 'aquamark', 'aquamark_general');
        add_settings_field('imageSizes', __('Apply to Image Sizes', 'aquamark'), [$this, 'renderImageSizesField'], 'aquamark', 'aquamark_general');
        add_settings_field('driver', __('Image Processing Driver', 'aquamark'), [$this, 'renderDriverField'], 'aquamark', 'aquamark_general');
    }

    public function sanitize(array $input): array
    {
        $oldWatermarkImageId = (int) get_option(self::OPTION_NAME)['watermarkImageId'] ?? 0;
        $newWatermarkImageId = isset($input['watermarkImageId']) ? (int) $input['watermarkImageId'] : 0;

        // If a new watermark image is selected
        if ($newWatermarkImageId !== $oldWatermarkImageId && $newWatermarkImageId !== 0) {
            // Temporarily remove the watermark filter to prevent the new watermark from being watermarked
            remove_filter('wp_generate_attachment_metadata', [$this->watermarkService, 'applyWatermark'], 10);

            // Regenerate metadata for the new watermark image to ensure it's clean
            $file = get_attached_file($newWatermarkImageId);
            if ($file) {
                wp_generate_attachment_metadata($newWatermarkImageId, $file);
            }

            // Re-add the watermark filter
            add_filter('wp_generate_attachment_metadata', [$this->watermarkService, 'applyWatermark'], 10, 2);
        }

        $input['watermarkImageId'] = $newWatermarkImageId;
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
        echo '<p>' . esc_html__('Configure the appearance and placement of the watermark.', 'aquamark') . '</p>';
    }

    public function renderWatermarkImageField(): void
    {
        $imageId = $this->options['watermarkImageId'] ?? 0;
        $imageUrl = $imageId ? wp_get_attachment_image_url($imageId, 'medium') : '';
        ?>
        <div class="aquamark-image-preview" style="margin-bottom: 10px;">
            <?php if ($imageUrl): ?>
                <img src="<?php echo esc_url($imageUrl); ?>" style="max-width: 200px; max-height: 200px;">
            <?php endif; ?>
        </div>
        <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[watermarkImageId]" value="<?php echo esc_attr($imageId); ?>">
        <button type="button" class="button button-secondary" id="aquamark-upload-button">
            <?php esc_html_e('Select Image', 'aquamark'); ?>
        </button>
        <button type="button" class="button button-secondary" id="aquamark-remove-button" style="display: <?php echo $imageId ? 'inline-block' : 'none'; ?>;">
            <?php esc_html_e('Remove Image', 'aquamark'); ?>
        </button>
        <?php
    }

    public function renderPositionField(): void
    {
        $position = $this->options['position'] ?? 'bottom-right';
        $positions = [
            'top-left' => __('Top Left', 'aquamark'), 'top-center' => __('Top Center', 'aquamark'), 'top-right' => __('Top Right', 'aquamark'),
            'middle-left' => __('Middle Left', 'aquamark'), 'middle-center' => __('Middle Center', 'aquamark'), 'middle-right' => __('Middle Right', 'aquamark'),
            'bottom-left' => __('Bottom Left', 'aquamark'), 'bottom-center' => __('Bottom Center', 'aquamark'), 'bottom-right' => __('Bottom Right', 'aquamark'),
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
        <p class="description"><?php esc_html_e('X and Y offset from the chosen position.', 'aquamark'); ?></p>
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
        <p class="description"><?php esc_html_e('Width and Height of the watermark. Set height to 0 for auto-scaling.', 'aquamark'); ?></p>
        <?php
    }

    public function renderOpacityField(): void
    {
        $opacity = $this->options['opacity'] ?? 80;
        ?>
        <input type="number" name="<?php echo esc_attr(self::OPTION_NAME); ?>[opacity]" value="<?php echo esc_attr($opacity); ?>" min="0" max="100" step="1">
        <p class="description"><?php esc_html_e('Opacity in percent (0-100).', 'aquamark'); ?></p>
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
        <p class="description"><?php esc_html_e('How the watermark blends with the image.', 'aquamark'); ?></p>
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
                    <?php echo esc_html($size); ?><?php echo esc_html($dimensions); ?>
                    <?php if ('full' === $size): ?>
                        <em style="color: red;">(<?php esc_html_e('Warning: Applying to full size is destructive and cannot be easily undone.', 'aquamark'); ?>)</em>
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
                <option value="auto" <?php selected('auto', $currentDriver); ?>><?php esc_html_e('Auto (Recommended)', 'aquamark'); ?></option>
                <option value="imagick" <?php selected('imagick', $currentDriver); ?>><?php esc_html_e('Imagick (High Quality)', 'aquamark'); ?></option>
                <option value="gd" <?php selected('gd', $currentDriver); ?>><?php esc_html_e('GD (Compatibility)', 'aquamark'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Choose the image processing library. Auto will use Imagick if available.', 'aquamark'); ?></p>
            <?php
        } elseif ($isImagickAvailable) {
            ?>
            <p><strong><?php esc_html_e('Imagick', 'aquamark'); ?></strong></p>
            <p class="description"><?php esc_html_e('Your server is using the Imagick library for high-quality image processing.', 'aquamark'); ?></p>
            <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[driver]" value="auto">
            <?php
        } elseif ($isGdAvailable) {
            ?>
            <p><strong><?php esc_html_e('GD', 'aquamark'); ?></strong></p>
            <p class="description"><?php esc_html_e('Your server is using the GD library. For higher quality, consider installing the Imagick extension.', 'aquamark'); ?></p>
            <input type="hidden" name="<?php echo esc_attr(self::OPTION_NAME); ?>[driver]" value="auto">
            <?php
        } else {
            ?>
            <p style="color: red;"><strong><?php esc_html_e('No compatible image processing library found!', 'aquamark'); ?></strong></p>
            <p class="description"><?php esc_html_e('This plugin requires either the GD or Imagick PHP extension to be installed.', 'aquamark'); ?></p>
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
                settings_fields('aquamark');
                do_settings_sections('aquamark');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
