<?php

namespace App\Services\Landing\Components;

final class SeedGalleryV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'seed-gallery-v1';
    }

    public function name(): string
    {
        return 'Seed Image Gallery';
    }

    public function category(): string
    {
        return 'seed landing';
    }

    public function view(): string
    {
        return 'landing.components.seed-gallery-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'heading' => ['type' => 'text', 'label' => 'Heading'],
                'images' => ['type' => 'repeater', 'label' => 'Images'],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => ['type' => 'color', 'label' => 'Background Color'],
            ]),
            'settings' => [
                'columns' => ['type' => 'number', 'label' => 'Desktop Columns', 'min' => 2, 'max' => 6],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'heading' => 'বারি বেগুন-১২ এর বাস্তব কিছু ছবি',
                'images' => [
                    ['url' => '', 'alt' => 'Bari-12 eggplants on plant'],
                    ['url' => '', 'alt' => 'Farmer holding Bari-12 eggplants'],
                    ['url' => '', 'alt' => 'Bari-12 seed packet'],
                    ['url' => '', 'alt' => 'Bari-12 eggplant seeds'],
                ],
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#ffffff',
            ]),
            'settings' => [
                'columns' => 4,
            ],
            'behaviours' => [],
            'data_source' => [],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.heading' => ['nullable', 'string', 'max:160'],
            'content.images' => ['nullable', 'array', 'max:12'],
            'content.images.*.url' => ['nullable', 'string', 'max:2048'],
            'content.images.*.alt' => ['nullable', 'string', 'max:500'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['required', 'array'],
            'settings.columns' => ['required', 'integer', 'min:2', 'max:6'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }
}
