# Free Watermarks: Developer Documentation

This document provides details on the WordPress hooks (actions and filters) available in the Free Watermarks plugin for programmatic customization.

## Filters

Filters allow you to modify data used by the plugin.

---

### `free_watermarks_settings`

Modifies the `Settings` object before it's used to apply a watermark. This is useful for changing settings dynamically based on the image being processed.

* **Parameters:**
    * `$settings` (`Williarin\FreeWatermarks\Settings\Settings`): The settings object.
    * `$attachmentId` (`int`): The ID of the attachment being processed.
* **Returns:** (`Williarin\FreeWatermarks\Settings\Settings`) The modified settings object.

**Example:** Disable watermarks for images uploaded by a specific user.

```php
add_filter('free_watermarks_settings', function ($settings, $attachmentId) {
    $authorId = get_post_field('post_author', $attachmentId);

    // Disable watermarks for user with ID 5
    if ($authorId == 5) {
        $settings->imageSizes = []; // Emptying imageSizes effectively disables the watermark
    }

    return $settings;
}, 10, 2);
````

-----

### `free_watermarks_watermark_image`

Modifies the `Imagine\Image\ImageInterface` object for the watermark itself before it is resized and applied. This allows for advanced manipulations like colorizing the watermark.

  * **Parameters:**
      * `$watermark` (`Imagine\Image\ImageInterface`): The Imagine image object for the watermark.
      * `$settings` (`Williarin\FreeWatermarks\Settings\Settings`): The current settings object.
  * **Returns:** (`Imagine\Image\ImageInterface`) The modified watermark image object.

**Example:** Convert the watermark to grayscale.

```php
add_filter('free_watermarks_watermark_image', function ($watermark, $settings) {
    $watermark->effects()->grayscale();
    return $watermark;
}, 10, 2);
```

-----

### `free_watermarks_position`

Modifies the calculated `Imagine\Image\Point` object that determines the top-left coordinate where the watermark will be placed.

  * **Parameters:**
      * `$position` (`Imagine\Image\Point`): The calculated X/Y coordinates for the watermark.
      * `$image` (`Imagine\Image\ImageInterface`): The base image being watermarked.
      * `$resizedWatermark` (`Imagine\Image\ImageInterface`): The resized watermark to be applied.
      * `$settings` (`Williarin\FreeWatermarks\Settings\Settings`): The current settings object.
  * **Returns:** (`Imagine\Image\Point`) The modified position object.

**Example:** Add a random "jitter" to the watermark position.

```php
use Imagine\Image\Point;

add_filter('free_watermarks_position', function ($position, $image, $resizedWatermark, $settings) {
    $newX = $position->getX() + rand(-10, 10);
    $newY = $position->getY() + rand(-10, 10);

    return new Point($newX, $newY);
}, 10, 4);
```

-----

## Actions

Actions allow you to run custom code at specific points during the watermarking process.

-----

### `free_watermarks_before_apply`

Fires just before the watermark is blended onto the base image.

  * **Parameters:**
      * `$image` (`Imagine\Image\ImageInterface`): The base image being watermarked.
      * `$resizedWatermark` (`Imagine\Image\ImageInterface`): The resized watermark to be applied.
      * `$settings` (`Williarin\FreeWatermarks\Settings\Settings`): The current settings object.

**Example:** Log information about the watermarking process.

```php
add_action('free_watermarks_before_apply', function ($image, $resizedWatermark, $settings) {
    error_log(sprintf(
        'Applying %dx%d watermark to %dx%d image.',
        $resizedWatermark->getSize()->getWidth(),
        $resizedWatermark->getSize()->getHeight(),
        $image->getSize()->getWidth(),
        $image->getSize()->getHeight()
    ));
}, 10, 3);
```

-----

### `free_watermarks_after_apply`

Fires immediately after the watermarked image has been saved to disk.

  * **Parameters:**
      * `$image` (`Imagine\Image\ImageInterface`): The final, modified image object.
      * `$settings` (`Williarin\FreeWatermarks\Settings\Settings`): The current settings object.

**Example:** Clear a specific cache or trigger a third-party service after an image is watermarked.

```php
add_action('free_watermarks_after_apply', function ($image, $settings) {
    if (function_exists('some_cache_clearing_function')) {
        some_cache_clearing_function();
    }
}, 10, 2);
```
