<?php

declare(strict_types=1);

namespace Williarin\AquaMark\Settings;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Williarin\AquaMark\BlendModeEnum;

final readonly class Settings
{
    public int $watermarkImageId;
    public string $position;
    public int $offsetX;
    public int $offsetY;
    public string $offsetUnit;
    public int $width;
    public int $height;
    public string $sizeUnit;
    public int $opacity;
    public string $blendMode;
    public array $imageSizes;
    public string $driver;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $resolvedOptions = $resolver->resolve($options);

        foreach ($resolvedOptions as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'watermarkImageId' => 0,
            'position' => 'bottom-right',
            'offsetX' => 10,
            'offsetY' => 10,
            'offsetUnit' => 'px',
            'width' => 150,
            'height' => 0, // 0 means auto
            'sizeUnit' => 'px',
            'opacity' => 80,
            'blendMode' => BlendModeEnum::Opacity->value,
            'imageSizes' => [],
            'driver' => 'auto',
        ])
            ->setAllowedTypes('watermarkImageId', 'int')
            ->setAllowedTypes('position', 'string')
            ->setAllowedTypes('offsetX', 'int')
            ->setAllowedTypes('offsetY', 'int')
            ->setAllowedTypes('offsetUnit', 'string')
            ->setAllowedTypes('width', 'int')
            ->setAllowedTypes('height', 'int')
            ->setAllowedTypes('sizeUnit', 'string')
            ->setAllowedTypes('opacity', 'int')
            ->setAllowedTypes('blendMode', 'string')
            ->setAllowedTypes('imageSizes', 'array')
            ->setAllowedTypes('driver', 'string')
            ->setAllowedValues('position', [
                'top-left', 'top-center', 'top-right',
                'middle-left', 'middle-center', 'middle-right',
                'bottom-left', 'bottom-center', 'bottom-right',
            ])
            ->setAllowedValues('offsetUnit', ['px', '%'])
            ->setAllowedValues('sizeUnit', ['px', '%'])
            ->setAllowedValues('blendMode', BlendModeEnum::values())
            ->setAllowedValues('opacity', range(0, 100))
            ->setAllowedValues('driver', ['auto', 'imagick', 'gd']);
    }
}
