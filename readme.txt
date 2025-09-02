=== AquaMark ===
Contributors: williarin
Tags: watermark, image, media, branding, free
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a custom watermark to your images in the WordPress media library with powerful controls and blending modes.

== Description ==

AquaMark is a full-featured, professional solution for applying image watermarks to your media uploads.
Built with a modern, robust architecture, it provides extensive control over the appearance and placement of your watermark, ensuring your brand is consistently represented across your site.
The plugin automatically uses the best available image processing library on your server (Imagick for quality, GD for compatibility) to deliver excellent results.

= Core Features =

* **Selectable Watermark Image**: Choose any image from your Media Library to use as a watermark.
* **Precise Positioning**: Place your watermark in any one of nine grid positions (top-left, middle-center, bottom-right, etc.).
* **Fine-Tuned Offset**: Adjust the watermark's final position with pixel (px) or percentage (%) based X and Y offsets.
* **Flexible Sizing**: Control the watermark's size with pixel (px) or percentage (%) based width and height. Supports auto-scaling.
* **Opacity Control**: Set the transparency of your watermark from 0% (fully transparent) to 100% (fully opaque).
* **Advanced Blend Modes**: Go beyond simple overlays with Photoshop-like blend modes, including Normal, Multiply, Screen, and Overlay.
* **Target Specific Image Sizes**: Choose exactly which registered image sizes should receive the watermark (e.g., thumbnail, medium, large).
* **Image Driver Selection**: Automatically uses the best image library available (Imagick or GD), with a manual override option for advanced users.
* **Destructive Action Warning**: The plugin warns you if you choose to apply the watermark to the 'full' size image, as this is a permanent, destructive action.
* **Regenerate Watermarks**: Easily apply new settings to existing images via a "Regenerate Watermarks" bulk action in the Media Library.
* **Remove Watermarks**: Remove watermarks from existing images via a "Remove Watermarks" bulk action in the Media Library.
* **Developer Friendly**: Includes WordPress actions and filters for programmatic extension and customization.
* **Modern & Conflict-Free**: Built with modern PHP and scoped dependencies to prevent conflicts with other plugins.

== Installation ==

1. Upload the `aquamark` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings > AquaMark** to configure the plugin.

== Frequently Asked Questions ==

= How do I select my watermark image? =

On the settings page, click the "Select Image" button. This will open the WordPress Media Library where you can upload or choose your desired watermark image.

= How do I apply watermarks to images I've already uploaded? =

Go to your Media Library, switch to the list view, select the images you want to update, choose "Regenerate Watermarks" from the "Bulk actions" dropdown, and click "Apply".

= How do I remove watermarks from images? =

Go to your Media Library, switch to the list view, select the images you want to remove watermarks from, choose "Remove Watermarks" from the "Bulk actions" dropdown, and click "Apply".

= What are Blend Modes? =

Blend modes change the way the watermark's pixels mix with the pixels of the underlying image, creating different artistic effects.
* **Normal**: The default mode, which simply overlays the watermark considering its opacity.
* **Multiply**: This mode multiplies the colors, resulting in a darker image. It's great for applying black or dark watermarks.
* **Screen**: The opposite of multiply, this mode results in a brighter image. It's effective for white or light-colored watermarks.
* **Overlay**: This mode combines Multiply and Screen, preserving highlights and shadows for a more natural-looking blend.

= What's the difference between pixel and percentage units? =

* **Pixels (px)** are a fixed size. A 10px offset will always be 10 pixels.
* **Percentage (%)** is relative to the dimensions of the image being watermarked. A 10% offset on a 1000px wide image will be 100px, while on a 500px image it will be 50px. This is useful for responsive sizing.

== Developer Documentation ==

The plugin includes a variety of actions and filters for programmatic customization. For detailed documentation and code examples, please see the `DEVELOPER.md` file included with the plugin.

== Screenshots ==

1. The main settings page, showing all available options.
2. An example of a watermarked image with the 'Multiply' blend mode.
3. The 'Regenerate Watermarks' bulk action in the Media Library.

== Changelog ==

= 1.0.0 =
* Initial release.
