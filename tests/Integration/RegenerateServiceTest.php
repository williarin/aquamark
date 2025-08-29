<?php

declare(strict_types=1);

namespace Plugin\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Plugin\Watermark\RegenerateService;

class RegenerateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset WP_Mock before each test
        \WP_Mock::tearDown();
        \WP_Mock::setUp();
    }

    protected function tearDown(): void
    {
        \WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testAddBulkActionAddsAction(): void
    {
        \WP_Mock::userFunction('__')
            ->andReturnUsing(function($text, $domain) { return $text; });

        $service = new RegenerateService();
        $actions = $service->addBulkAction([]);
        self::assertArrayHasKey('regenerate_watermarks', $actions);
        self::assertSame('Regenerate Watermarks', $actions['regenerate_watermarks']);
    }

    public function testHandleBulkActionDoesNothingForOtherActions(): void
    {
        $service = new RegenerateService();
        $redirectTo = 'admin.php?page=upload';

        // Mock add_query_arg as it's a WordPress function
        \WP_Mock::userFunction('add_query_arg')
            ->never();

        $result = $service->handleBulkAction($redirectTo, 'other_action', [1, 2]);
        self::assertSame($redirectTo, $result);
    }

    public function testHandleBulkActionRegeneratesImages(): void
    {
        \WP_Mock::userFunction('wp_attachment_is_image')
            ->andReturnUsing(function($id) {
                // Return true for IDs 1 and 3, false for ID 2
                return in_array($id, [1, 3]);
            });

        \WP_Mock::userFunction('get_attached_file')
            ->andReturnUsing(function($id) {
                // Return file paths for IDs 1 and 3
                if ($id === 1) return '/path/to/image1.jpg';
                if ($id === 3) return '/path/to/image3.png';
                return false;
            });

        // Mock wp_generate_attachment_metadata to assert it's called
        \WP_Mock::userFunction('wp_generate_attachment_metadata')
            ->andReturnUsing(function($attachment_id, $file) {
                $GLOBALS['wp_generate_attachment_metadata_calls'][] = [$attachment_id, $file];
                return []; // Return empty array as it's not used in this test
            });

        \WP_Mock::userFunction('add_query_arg')
            ->andReturnUsing(function($key, $value, $url) {
                return $url . '&' . $key . '=' . $value;
            });

        $service = new RegenerateService();
        $redirectTo = 'admin.php?page=upload';
        $postIds = [1, 2, 3];

        $result = $service->handleBulkAction($redirectTo, 'regenerate_watermarks', $postIds);

        self::assertCount(2, $GLOBALS['wp_generate_attachment_metadata_calls']);
        self::assertSame([1, '/path/to/image1.jpg'], $GLOBALS['wp_generate_attachment_metadata_calls'][0]);
        self::assertSame([3, '/path/to/image3.png'], $GLOBALS['wp_generate_attachment_metadata_calls'][1]);

        self::assertSame('admin.php?page=upload&regenerated=2', $result);
    }

    public function testDisplayAdminNotice(): void
    {
        $_REQUEST['regenerated'] = 5;

        // _n and printf are internal PHP functions, not mocked by WP_Mock
        // We will assert the output directly
        $this->expectOutputString('<div class="notice notice-success is-dismissible"><p>5 image had its watermark regenerated.</p></div>');

        $service = new RegenerateService();
        $service->displayAdminNotice();

        unset($_REQUEST['regenerated']);
    }
}