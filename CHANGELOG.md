# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-08-30

### Added

- Initial release of the Free Watermarks plugin.
- Feature: Select watermark image from the WordPress Media Library.
- Feature: Nine-point grid for watermark positioning.
- Feature: Pixel (px) and percentage (%) based X/Y offsets.
- Feature: Pixel (px) and percentage (%) based width/height sizing with auto-scaling.
- Feature: Opacity control from 0-100%.
- Feature: Advanced blend modes: Normal, Multiply, Screen, and Overlay.
- Feature: Ability to target specific registered image sizes.
- Feature: Manual and automatic selection of image processing driver (Imagick/GD).
- Feature: Warning when applying watermark to the destructive 'full' size.
- Feature: "Regenerate Watermarks" bulk action in the Media Library to apply new settings or remove watermarks.
- Feature: Developer hooks (`actions` and `filters`) for programmatic customization.
- Architecture: Modern OOP using PHP 8.2, PSR-4 autoloading, and a Dependency Injection container.
- Architecture: Scoped dependencies via `php-scoper` to prevent plugin conflicts.
- Development: `Makefile` for simplified build and release process.
- Testing: Unit and integration test suite for core features.
