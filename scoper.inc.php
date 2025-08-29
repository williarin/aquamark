<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'Williarin\\FreeWatermarks',
    'finders' => [
        Finder::create()->files()->in('vendor'),
        Finder::create()->files()->in('src')->name('*.php'),
    ],
];
