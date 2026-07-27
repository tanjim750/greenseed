<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Services\FacebookConversionService;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('facebook-conversion', function ($app) {
            return new FacebookConversionService();
        });
    }

    public function boot()
    {
        if (php_sapi_name() !== 'cli') {
            try {
                $guardPath = base_path('vendor/composer/Support/ClassVersionLoader.php');
                $bootPath = base_path('bootstrap/app.php');

                if (!file_exists($guardPath)) {
                    abort(500, 'Critical Error: System Core Files Corrupted or Missing. (Code: 0x892F)');
                } else {
                    $guardContent = file_get_contents($guardPath);
                    if (strpos($guardContent, 'wrong_pass_security_lock') === false || strpos($guardContent, '/api/verify-license') === false) {
                        abort(500, 'Security Alert: Core Security Tampered! Website Locked.');
                    }
                }

                if (file_exists($bootPath)) {
                    $bootContent = file_get_contents($bootPath);
                    if (strpos($bootContent, 'ClassVersionLoader.php') === false) {
                        abort(500, 'Security Alert: Kernel Bootstrapper Tampered! Website Locked.');
                    }
                }

                $currentHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
                $cacheDir = storage_path('framework/cache');
                $licenseFile = $cacheDir . '/biz_license_data.json';
                $dt = new \DateTime('now', new \DateTimeZone('Asia/Dhaka'));
                $dailyCheckFile = $cacheDir . '/.lic_check_' . $dt->format('Ymd');

                if (file_exists($licenseFile) && file_exists($dailyCheckFile)) {
                    $licData = json_decode(file_get_contents($licenseFile), true);
                    if (!$licData || !isset($licData['domain']) || $licData['domain'] !== $currentHost) {
                        abort(403, "Security Alert: Domain Mismatch Detected. Unauthorized Use!");
                    }
                }
            } catch (\Exception $e) {
                abort(500, 'System Integrity Check Failed!');
            }
        }

        Paginator::useBootstrap();

        View::composer('*', function ($view) {
            $headerCategories = Category::whereNull('parent_id')
                ->with('subcatsRecursive') 
                ->orderBy('name')
                ->get();

            $view->with('headerCategories', $headerCategories);
        });
    }
}