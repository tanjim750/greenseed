<?php

namespace App\Services\Landing\Components;

final class SeedMobileWhyBuyV1 extends SeedMobileChecklistV1
{
    protected function componentKey(): string
    {
        return 'seed-mobile-why-buy-v1';
    }

    protected function componentName(): string
    {
        return 'Seed Mobile Why Buy Checklist';
    }

    protected function defaultHeading(): string
    {
        return 'আমাদের থেকে কেন বীজ নিবেন?';
    }

    protected function defaultItems(): array
    {
        return [
            ['icon' => 'verified', 'text' => 'প্রতিটি বেগুন গড়ে ১ কেজি ওজনের হয়।'],
            ['icon' => 'verified', 'text' => 'বাছাইকৃত A গ্রেড ও হাইব্রিড বীজ।'],
            ['icon' => 'verified', 'text' => 'কোন ডেলিভারি চার্জ নেই, সম্পূর্ণ ফ্রি।'],
        ];
    }
}
