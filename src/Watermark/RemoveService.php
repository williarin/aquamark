<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Watermark;

final class RemoveService
{
    public function __construct(
        private readonly WatermarkService $watermarkService
    ) {
    }

    public function register(): void
    {
        add_filter('bulk_actions-upload', [$this, 'addBulkAction']);
        add_filter('handle_bulk_actions-upload', [$this, 'handleBulkAction'], 10, 3);
        add_action('admin_notices', [$this, 'displayAdminNotice']);
    }

    public function addBulkAction(array $bulkActions): array
    {
        $bulkActions['remove_watermarks'] = __('Remove Watermarks', 'free-watermarks');
        return $bulkActions;
    }

    public function handleBulkAction(string $redirectTo, string $action, array $postIds): string
    {
        if ($action !== 'remove_watermarks') {
            return $redirectTo;
        }

        remove_filter('wp_generate_attachment_metadata', [$this->watermarkService, 'applyWatermark'], 10);

        $removedCount = 0;
        foreach ($postIds as $postId) {
            if (wp_attachment_is_image($postId)) {
                $file = get_attached_file($postId);
                if ($file) {
                    wp_generate_attachment_metadata($postId, $file);
                    $removedCount++;
                }
            }
        }

        add_filter('wp_generate_attachment_metadata', [$this->watermarkService, 'applyWatermark'], 10, 2);

        return add_query_arg('removed', $removedCount, $redirectTo);
    }

    public function displayAdminNotice(): void
    {
        if (!empty($_REQUEST['removed'])) {
            $count = (int) $_REQUEST['removed'];
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(_n(
                    '%d image had its watermark removed.',
                    '%d images had their watermarks removed.',
                    $count,
                    'free-watermarks'
                ), $count)
            );
        }
    }
}
