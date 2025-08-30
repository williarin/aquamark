<?php

declare(strict_types=1);

namespace Williarin\FreeWatermarks\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Williarin\FreeWatermarks\Settings\Settings;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class SettingsTest extends TestCase
{
    public function testDefaults(): void
    {
        $settings = new Settings([]);

        self::assertSame(0, $settings->watermarkImageId);
        self::assertSame('bottom-right', $settings->position);
        self::assertSame(80, $settings->opacity);
        self::assertSame([], $settings->imageSizes);
    }

    public function testValidOptionsAreSet(): void
    {
        $options = [
            'watermarkImageId' => 123,
            'position' => 'top-left',
            'opacity' => 100,
            'imageSizes' => ['thumbnail', 'medium'],
        ];

        $settings = new Settings($options);

        self::assertSame(123, $settings->watermarkImageId);
        self::assertSame('top-left', $settings->position);
        self::assertSame(100, $settings->opacity);
        self::assertSame(['thumbnail', 'medium'], $settings->imageSizes);
    }

    public function testInvalidPositionThrowsException(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new Settings(['position' => 'top-middle']);
    }

    public function testInvalidOpacityThrowsException(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new Settings(['opacity' => 101]);
    }
}
