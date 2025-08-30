<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Watermark;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Williarin\FreeWatermarks\Admin\SettingsPage;
use Williarin\FreeWatermarks\Image\Blender\BlenderInterface;
use Williarin\FreeWatermarks\Settings\Settings;

final class WatermarkService
{
    public function __construct(
        private readonly ImagineInterface $imagine,
        private readonly BlenderInterface $blender
    ) {
    }

    public function register(): void
    {
        add_filter('wp_generate_attachment_metadata', [$this, 'applyWatermark'], 10, 2);
    }

    public function applyWatermark(array $metadata, int $attachmentId): array
    {
        $options = get_option(SettingsPage::OPTION_NAME, []);
        $baseSettings = new Settings($options);

        /**
         * Filter the watermark settings before applying.
         * @param Settings $settings The settings object.
         * @param int $attachmentId The ID of the attachment being processed.
         */
        $settings = apply_filters('free_watermarks_settings', $baseSettings, $attachmentId);

        // Prevent the watermark image itself from being watermarked
        if ($attachmentId === $settings->watermarkImageId) {
            return $metadata;
        }

        if (empty($settings->imageSizes) || !$settings->watermarkImageId || !wp_attachment_is_image($attachmentId)) {
            return $metadata;
        }

        $watermarkPath = get_attached_file($settings->watermarkImageId);
        if (!$watermarkPath || !file_exists($watermarkPath)) {
            return $metadata;
        }

        $uploadDir = wp_get_upload_dir();

        try {
            $watermarkBase = $this->imagine->open($watermarkPath);

            /**
             * Filter the watermark image object before processing.
             * @param ImageInterface $watermark The Imagine image object for the watermark.
             * @param Settings $settings The settings object.
             */
            $watermark = apply_filters('free_watermarks_watermark_image', $watermarkBase, $settings);

            $this->applyOpacity($watermark, $settings->opacity);

            foreach ($settings->imageSizes as $size) {
                $imagePath = $this->getImagePath($metadata, $size, $uploadDir);

                if (!$imagePath) {
                    continue;
                }

                $image = $this->imagine->open($imagePath);
                $resizedWatermark = $this->resizeWatermark($watermark, $image, $settings);
                $basePosition = $this->calculatePosition($image, $resizedWatermark, $settings);

                /**
                 * Filter the calculated position of the watermark.
                 * @param Point $position The calculated position.
                 * @param ImageInterface $image The base image.
                 * @param ImageInterface $resizedWatermark The resized watermark.
                 * @param Settings $settings The settings object.
                 */
                $position = apply_filters('free_watermarks_position', $basePosition, $image, $resizedWatermark, $settings);

                /**
                 * Action before the watermark is applied.
                 * @param ImageInterface $image The base image.
                 * @param ImageInterface $resizedWatermark The resized watermark.
                 * @param Settings $settings The settings object.
                 */
                do_action('free_watermarks_before_apply', $image, $resizedWatermark, $settings);

                $this->blender->blend($settings->blendMode, $image, $resizedWatermark, $position, $settings->opacity);

                $image->save($imagePath);

                /**
                 * Action after the watermark has been applied and saved.
                 * @param ImageInterface $image The modified image.
                 * @param Settings $settings The settings object.
                 */
                do_action('free_watermarks_after_apply', $image, $settings);
            }
        } catch (\Exception $e) {
            error_log('Free Watermarks Error: ' . $e->getMessage());
        }

        return $metadata;
    }

    private function getImagePath(array $metadata, string $size, array $uploadDir): ?string
    {
        $path = null;
        if ($size === 'full') {
            $path = $uploadDir['basedir'] . '/' . $metadata['file'];
        } elseif (isset($metadata['sizes'][$size])) {
            $path = $uploadDir['path'] . '/' . $metadata['sizes'][$size]['file'];
        }

        return $path && file_exists($path) ? $path : null;
    }

    private function applyOpacity(ImageInterface $image, int $opacity): void
    {
        // Opacity is handled when pasting the watermark, not by modifying the watermark image directly
        // We'll pass the opacity value to the blender instead
    }

    private function resizeWatermark(ImageInterface $watermark, ImageInterface $image, Settings $settings): ImageInterface
    {
        $imageSize = $image->getSize();
        $watermarkRatio = $watermark->getSize()->getWidth() / $watermark->getSize()->getHeight();

        if ($settings->sizeUnit === '%') {
            $newWidth = (int) ($imageSize->getWidth() * ($settings->width / 100));
            $newHeight = $settings->height > 0 ? (int)($imageSize->getHeight() * ($settings->height / 100)) : 0;
        } else {
            $newWidth = $settings->width;
            $newHeight = $settings->height;
        }

        if ($newHeight === 0 && $watermarkRatio > 0) {
            $newHeight = (int) ($newWidth / $watermarkRatio);
        }

        if ($newWidth === 0 && $watermarkRatio > 0) {
            $newWidth = (int) ($newHeight * $watermarkRatio);
        }

        return $watermark->copy()->resize(new Box($newWidth, $newHeight), ImageInterface::FILTER_LANCZOS);
    }

    private function calculatePosition(ImageInterface $image, ImageInterface $watermark, Settings $settings): Point
    {
        $imageSize = $image->getSize();
        $watermarkSize = $watermark->getSize();

        [$x, $y] = match ($settings->position) {
            'top-left' => [0, 0],
            'top-center' => [(int) (($imageSize->getWidth() - $watermarkSize->getWidth()) / 2), 0],
            'top-right' => [$imageSize->getWidth() - $watermarkSize->getWidth(), 0],
            'middle-left' => [0, (int) (($imageSize->getHeight() - $watermarkSize->getHeight()) / 2)],
            'middle-center' => [(int) (($imageSize->getWidth() - $watermarkSize->getWidth()) / 2), (int) (($imageSize->getHeight() - $watermarkSize->getHeight()) / 2)],
            'middle-right' => [$imageSize->getWidth() - $watermarkSize->getWidth(), (int) (($imageSize->getHeight() - $watermarkSize->getHeight()) / 2)],
            'bottom-left' => [0, $imageSize->getHeight() - $watermarkSize->getHeight()],
            'bottom-center' => [(int) (($imageSize->getWidth() - $watermarkSize->getWidth()) / 2), $imageSize->getHeight() - $watermarkSize->getHeight()],
            default => [$imageSize->getWidth() - $watermarkSize->getWidth(), $imageSize->getHeight() - $watermarkSize->getHeight()],
        };

        $offsetX = ($settings->offsetUnit === '%') ? (int) ($imageSize->getWidth() * ($settings->offsetX / 100)) : $settings->offsetX;
        $offsetY = ($settings->offsetUnit === '%') ? (int) ($imageSize->getHeight() * ($settings->offsetY / 100)) : $settings->offsetY;

        return new Point($x + $offsetX, $y + $offsetY);
    }
}
