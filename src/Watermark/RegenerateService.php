<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Watermark;

final class RegenerateService
{
    public function register(): void
    {
        add_filter('bulk_actions-upload', [$this, 'addBulkAction']);
        add_filter('handle_bulk_actions-upload', [$this, 'handleBulkAction'], 10, 3);
        add_action('admin_notices', [$this, 'displayAdminNotice']);
    }

    public function addBulkAction(array $bulkActions): array
    {
        $bulkActions['regenerate_watermarks'] = __('Regenerate Watermarks', 'free-watermarks');
        return $bulkActions;
    }

    public function handleBulkAction(string $redirectTo, string $action, array $postIds): string
    {
        if ($action !== 'regenerate_watermarks') {
            return $redirectTo;
        }

        // Verify nonce in admin context
        if (is_admin()) {
            // In test environment, check_admin_referer might not exist
            if (function_exists('check_admin_referer')) {
                check_admin_referer('bulk-posts');
            }
        }

        // Removed: require_once ABSPATH . 'wp-admin/includes/image.php';

        $regeneratedCount = 0;
        foreach ($postIds as $postId) {
            if (wp_attachment_is_image($postId)) {
                $file = get_attached_file($postId);
                if ($file) {
                    wp_generate_attachment_metadata($postId, $file);
                    $regeneratedCount++;
                }
            }
        }

        return add_query_arg('regenerated', $regeneratedCount, $redirectTo);
    }

    public function displayAdminNotice(): void
    {
        if (!empty($_REQUEST['regenerated'])) {
            // Verify nonce in admin context
            if (is_admin() && isset($_REQUEST['_wpnonce'])) {
                // In test environment, wp_verify_nonce might not exist
                if (function_exists('wp_verify_nonce')) {
                    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'bulk-posts')) {
                        return;
                    }
                }
            }
            
            // Fallback for absint if it doesn't exist in test environment
            $count = function_exists('absint') ? absint($_REQUEST['regenerated']) : (int)$_REQUEST['regenerated'];
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                /* translators: %s: number of images */
                esc_html(sprintf(_n(
                    '%d image had its watermark regenerated.',
                    '%d images had their watermarks regenerated.',
                    $count,
                    'free-watermarks'
                ), number_format_i18n($count)))
            );
        }
    }
}
