<?php

namespace App\Services\Landing\Components;

final class HeroCountdownV1 extends BaseLandingComponent
{
    public function key(): string
    {
        return 'hero-countdown-v1';
    }

    public function name(): string
    {
        return 'Hero With Countdown';
    }

    public function category(): string
    {
        return 'hero';
    }

    public function view(): string
    {
        return 'landing.components.hero-countdown-v1';
    }

    public function schema(): array
    {
        return [
            'content' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Title',
                    'required' => true,
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => 'Description',
                ],
                'button_text' => [
                    'type' => 'text',
                    'label' => 'Button Text',
                ],
                'button_url' => [
                    'type' => 'url',
                    'label' => 'Button URL',
                ],
            ],
            'style' => array_merge($this->commonStyleSchema(), [
                'background_color' => [
                    'type' => 'color',
                    'label' => 'Background Color',
                ],
                'title_color' => [
                    'type' => 'color',
                    'label' => 'Title Color',
                ],
                'button_color' => [
                    'type' => 'color',
                    'label' => 'Button Color',
                ],
            ]),
            'settings' => [
                'countdown.duration_hours' => [
                    'type' => 'number',
                    'label' => 'Countdown Hours',
                    'min' => 1,
                ],
                'countdown.starts_at' => [
                    'type' => 'datetime',
                    'label' => 'Starts At',
                ],
            ],
        ];
    }

    public function defaults(): array
    {
        return [
            'content' => [
                'title' => 'Limited Time Offer',
                'description' => null,
                'button_text' => 'Order Now',
                'button_url' => '#',
            ],
            'style' => array_merge($this->commonStyleDefaults(), [
                'background_color' => '#0f172a',
                'title_color' => '#ffffff',
                'button_color' => '#2563eb',
            ]),
            'settings' => [
                'countdown' => [
                    'duration_hours' => 72,
                    'starts_at' => null,
                ],
            ],
            'behaviours' => [],
            'data_source' => [],
        ];
    }

    public function validationRules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.title' => ['required', 'string', 'max:160'],
            'content.description' => ['nullable', 'string', 'max:1000'],
            'content.button_text' => ['nullable', 'string', 'max:50'],
            'content.button_url' => ['nullable', 'string', 'max:2048'],
            'style' => ['required', 'array'],
            'style.background_color' => ['required'],
            'style.title_color' => ['required'],
            'style.button_color' => ['required'],
            'style.*' => [$this->styleValueRule()],
            'settings' => ['required', 'array'],
            'settings.countdown' => ['required', 'array'],
            'settings.countdown.duration_hours' => ['required', 'integer', 'min:1', 'max:8760'],
            'settings.countdown.starts_at' => ['nullable', 'date'],
            'behaviours' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }

    public function behaviours(): array
    {
        return [
            'recurring-countdown',
        ];
    }
}
