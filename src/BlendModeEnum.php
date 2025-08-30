<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks;

enum BlendModeEnum: string
{
    case Opacity = 'opacity';
    case Multiply = 'multiply';
    case Screen = 'screen';
    case Overlay = 'overlay';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
