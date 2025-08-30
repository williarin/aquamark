<?php

// First, load the autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Initialize WP_Mock
\WP_Mock::bootstrap();

// All mock functions are now handled by WP_Mock itself.
