<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $slug = 'bari-12-eggplant-seeds';

    public function up(): void
    {
        if (
            !Schema::hasTable('dynamic_landing_pages')
            || !Schema::hasTable('dynamic_landing_page_components')
        ) {
            return;
        }

        $now = now();

        DB::table('dynamic_landing_pages')->updateOrInsert(
            ['slug' => $this->slug],
            [
                'name' => 'Bari-12 Eggplant Seeds',
                'status' => 'draft',
                'theme' => $this->json([
                    'primary' => '#0d631b',
                    'secondary' => '#006e1c',
                    'background' => '#faf9f5',
                    'surface' => '#ffffff',
                    'text' => '#1a1c1a',
                    'muted_text' => '#64748b',
                ]),
                'seo' => $this->json([
                    'title' => 'Green Seed BD - Bari-12 Eggplant Seeds',
                    'description' => 'বারি বেগুন-১২ প্রিমিয়াম বীজের অফার, সুবিধা, ছবি এবং অর্ডার ফর্ম।',
                ]),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $pageId = DB::table('dynamic_landing_pages')
            ->where('slug', $this->slug)
            ->value('id');

        if (!$pageId) {
            return;
        }

        DB::table('dynamic_landing_page_components')
            ->where('dynamic_landing_page_id', $pageId)
            ->delete();

        foreach ($this->components($pageId, $now) as $component) {
            DB::table('dynamic_landing_page_components')->insert($component);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('dynamic_landing_pages')) {
            return;
        }

        $pageId = DB::table('dynamic_landing_pages')
            ->where('slug', $this->slug)
            ->value('id');

        if ($pageId && Schema::hasTable('dynamic_landing_page_components')) {
            DB::table('dynamic_landing_page_components')
                ->where('dynamic_landing_page_id', $pageId)
                ->delete();
        }

        DB::table('dynamic_landing_pages')
            ->where('slug', $this->slug)
            ->delete();
    }

    private function components(int $pageId, mixed $now): array
    {
        return [
            $this->component($pageId, 'seed-offer-hero-v1', 'cmp_bari12_hero', 1, [
                'content' => [
                    'badge_text' => 'সীমিত সময়ের অফার!',
                    'title' => 'বারি বেগুন-১২ এর প্রিমিয়াম বীজ এখন আরও সুলভে',
                    'description' => 'প্রতিটি বেগুন ১ কেজি পর্যন্ত ওজনের হতে পারে। উচ্চ ফলনশীল ও লবনাক্ততা সহিষ্ণু উন্নত জাতের বীজ সরাসরি আপনার দুয়ারে।',
                    'offer_label' => 'অফার মূল্য',
                    'price' => '৳৩০০',
                    'old_price' => '৳৪০০',
                    'timer_label' => 'অফারটি শেষ হবে',
                    'image_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDjhj8OnzznHv4rTS22A5ZsKNywZnqtgMYxwp9RxSW5qjm3Brz1SzhBOnJdrXMx6kk3_g9k9YpSXDEHa0qdNjpu8v1SM4U8pdUltr2rjBxMY_7KlrniP9c6KeBV0Tb9LM-BXeHcoC1cy1x0_kmB6rsGllef1uY1lNX_LSy8AHKIPO5D2Jwy3eakzSLDDDIF5NNeoF2jFVilhBc7rrIk2oJCzZoZcn0H2ujiD91E3YpfsQbvZodGqn5p5g',
                    'image_alt' => 'A large Bari-12 eggplant held by a farmer in a green field.',
                    'trust_badge' => '১০০% গ্যারান্টি',
                ],
                'style' => [
                    'background_color' => '#0d631b',
                    'accent_color' => '#ffb300',
                    'text_color' => '#ffffff',
                ],
                'settings' => [
                    'countdown' => [
                        'duration_hours' => 4,
                        'starts_at' => null,
                    ],
                ],
            ], $now),
            $this->component($pageId, 'seed-benefits-v1', 'cmp_bari12_benefits', 2, [
                'content' => [
                    'heading' => 'কেন বারি বেগুন-১২ চাষ করবেন?',
                    'feature_title' => 'উচ্চ ফলনশীল ও পুষ্টিগুণে ভরপুর',
                    'feature_description' => 'বারি বেগুন-১২ একটি নতুন জাতের উন্নত বেগুন, যা লবনাক্ত জমিতেও সফলভাবে চাষ করা যায়। এটি রোগ প্রতিরোধে সক্ষম এবং দীর্ঘ সময় ফলন দেয়।',
                    'feature_points' => [
                        'প্রতিটি বেগুনের ওজন ৮০০ গ্রাম থেকে ১ কেজি পর্যন্ত হয়।',
                        'লবনাক্ততা এবং উচ্চ তাপমাত্রায় ফলন ভালো হয়।',
                    ],
                    'cards' => [
                        ['icon' => 'local_shipping', 'title' => 'ফ্রি ডেলিভারি', 'description' => 'সারা বাংলাদেশে দ্রুত এবং সম্পূর্ণ বিনামূল্যে ডেলিভারি চার্জ ছাড়া হোম ডেলিভারি।'],
                        ['icon' => 'menu_book', 'title' => 'গাইডলাইন বই', 'description' => 'বীজ রোপন পদ্ধতি ও পরিচর্যার জন্য একটি বিস্তারিত গাইডলাইন বই উপহার।'],
                    ],
                    'trust_cards' => [
                        ['icon' => 'verified', 'title' => '১০০% অরিজিনাল বীজ', 'description' => 'আমরা সরাসরি বিশ্বস্ত উৎস থেকে সংগৃহীত এ গ্রেড কোয়ালিটির হাইব্রিড বীজ সরবরাহ করি।'],
                        ['icon' => 'sentiment_satisfied', 'title' => 'মানি ব্যাক গ্যারান্টি', 'description' => 'বীজ না গজালে টাকা ফেরতের ১০০% নিশ্চয়তা দিচ্ছি আমরা।'],
                    ],
                ],
                'style' => [
                    'background_color' => '#faf9f5',
                    'accent_color' => '#ffb300',
                ],
                'settings' => [],
            ], $now),
            $this->component($pageId, 'seed-gallery-v1', 'cmp_bari12_gallery', 3, [
                'content' => [
                    'heading' => 'বারি বেগুন-১২ এর বাস্তব কিছু ছবি',
                    'images' => [
                        ['url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCBQNgMJi6Fwusa732E5wN1DW_mJQj9RnHDDDD9TIROfFCdNBoT1iLFqxi3rn8RHn9amQVeeMGmHpsvW3W-3nJFk1IK9UpFPARXUV233Z_clyF1t5viWvQ6M40RuyBoaEJxqLGr_SPPqHBnSuEVkt0NcjitS7iyith9e1yY0dd6Zh5O0Yhey4cpanle0GnJyAGDjVKF6O9oZominghXrzgb5GQMFteIOpgf_hfYUMAMN5ctndk495CWIg', 'alt' => 'Bari-12 eggplants on a plant.'],
                        ['url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBX2Pxmq1ftGyksXO0Lq4DvIeAPIWXmYYrGseCybV_1Bo142686P6-S2ATsD3EewOSN0yc_CvwE9jC0PiqWXZVTrlRobuZowgHA0feFzt-nl5JB026P-VULwkHJPHoMkmy2zPCAKxOJ8UXGfuUGsQlCgx7Um93-iB2rsDFL28Hb5hWzrzHSpEjN0UIwCD94jGB6KihyEI6Q4WlhNlstWgjSkF5VM0jmDpRmh-17ukSFQHF7tbJLC5jrww', 'alt' => 'Farmer holding harvested Bari-12 eggplants.'],
                        ['url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBQUSHuUBSuVsvcONej0UW-76AExdXi5LvQK5oi2n6pW-lR1BqSCW1BVG-xWQbB856moGROKRBWnS34t3LAJ09BXg7wMm-h_6Q5keQ_yOJECRrCEP-7gP9gizjQT1Kcnl1ZAqNeYPvlbGl8ge-ebv7v5sTrlGNlefLBFc3MDg5kDozjtopz9qjwXEJkg8UdizpOSUNn__en2t4kviZ99WgQw3krPHKHWd6uvQ23C3ceEgkUSIrKygxFLQ', 'alt' => 'Seed packet and gardening tools.'],
                        ['url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAPKDbqTUxP8f2mv5kOyV1IRb4UGHvhBL1gVgJj6IHBOzNlpQoqAOhDqPU2IJTsx9zCwFz_8E0jRG-dJCoijaY_lzzgp8pwie0nIBVOpDl6mJm1Ajwpy985XKkwq2eg9xffn-S9i1pwX0fZ6b5bFhPsvI-PC4oTCJPFuLgPsREEdgQhQSt8eUa4bLBX5GLiuA2ZXaB6n2qNUP627iRMm5nVcgeIRSKbiQ7VrdJCmipon8ezqAvG9kPjgQ', 'alt' => 'Macro photograph of Bari-12 seeds.'],
                    ],
                ],
                'style' => [
                    'background_color' => '#faf9f5',
                ],
                'settings' => [
                    'columns' => 4,
                ],
            ], $now),
            $this->component($pageId, 'seed-checkout-v1', 'cmp_bari12_checkout', 4, [
                'content' => [
                    'heading' => 'অর্ডার করতে নিচের ফর্মটি সঠিক ভাবে পূরণ করুন',
                    'customer_heading' => 'আপনার তথ্য দিন',
                    'product_heading' => 'পণ্য নির্বাচন করুন',
                    'delivery_title' => 'ডেলিভারি চার্জ সম্পূর্ণ ফ্রি!',
                    'delivery_description' => 'অর্ডার কনফার্ম করার পর ২-৩ দিনের মধ্যে হোম ডেলিভারি পাবেন ইনশাআল্লাহ।',
                    'packages' => [
                        ['quantity' => 1, 'title' => '১ প্যাকেট বারি-১২ বেগুনের বীজ', 'subtitle' => '+ ১ প্যাকেট শসা বীজ ফ্রি', 'price' => '৳৩০০'],
                        ['quantity' => 2, 'title' => '২ প্যাকেট বারি-১২ বেগুনের বীজ', 'subtitle' => '+ ২ প্যাকেট শসা বীজ ফ্রি', 'price' => '৳৫৫০'],
                    ],
                    'summary_title' => 'অর্ডার সামারি',
                    'payment_note' => 'পেমেন্ট মাধ্যম: ক্যাশ অন ডেলিভারি (পণ্য বুঝে পেয়ে টাকা দিন)',
                    'button_text' => 'অর্ডার সম্পন্ন করুন',
                ],
                'style' => [
                    'background_color' => '#eeeeea',
                    'button_color' => '#0d631b',
                ],
                'settings' => [
                    'default_quantity' => 1,
                ],
                'data_source' => [
                    'product_ids' => [],
                ],
            ], $now),
            $this->component($pageId, 'seed-support-v1', 'cmp_bari12_support', 5, [
                'content' => [
                    'heading' => 'প্রয়োজনে কল বা হোয়াটসঅ্যাপ করুন',
                    'phone' => '01897926161',
                    'badges' => [
                        ['icon' => 'local_shipping', 'text' => 'দেশজুড়ে ফ্রি শিপিং'],
                        ['icon' => 'shield', 'text' => 'সুরক্ষিত পেমেন্ট'],
                        ['icon' => 'history', 'text' => '৭ দিনের রিপ্লেসমেন্ট'],
                    ],
                ],
                'style' => [
                    'button_color' => '#006e1c',
                ],
                'settings' => [],
            ], $now),
            $this->component($pageId, 'seed-footer-v1', 'cmp_bari12_footer', 6, [
                'content' => [
                    'brand' => 'Green Seed BD',
                    'description' => '© 2024 Green Seed BD. Growth, precision, and earth-bound reliability.',
                    'links' => [
                        ['label' => 'Privacy Policy', 'url' => '#'],
                        ['label' => 'Terms of Service', 'url' => '#'],
                        ['label' => 'Shipping Info', 'url' => '#'],
                        ['label' => 'Contact Us', 'url' => '#'],
                    ],
                ],
                'style' => [
                    'background_color' => '#e2e3df',
                ],
                'settings' => [],
            ], $now),
        ];
    }

    private function component(int $pageId, string $key, string $scope, int $sortOrder, array $config, mixed $now): array
    {
        return [
            'dynamic_landing_page_id' => $pageId,
            'component_key' => $key,
            'instance_scope' => $scope,
            'sort_order' => $sortOrder,
            'config' => $this->json(array_replace_recursive([
                'content' => [],
                'style' => [],
                'settings' => [],
                'behaviours' => [],
                'data_source' => [],
            ], $config)),
            'is_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
};
