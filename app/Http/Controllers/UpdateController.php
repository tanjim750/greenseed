<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class UpdateController extends Controller
{
    private function getValidLicenseKey()
    {
        // ✅ Changed location to 'app' folder to prevent deletion on cache clear
        $licenseFile = storage_path('app/biz_license_data.json'); 
        
        if (File::exists($licenseFile)) {
            $licenseData = json_decode(File::get($licenseFile), true);
            return $licenseData['license_key'] ?? null;
        }
        
        return null;
    }

    public function checkUpdate()
    {
        try {
            $currentVersion = config('updater.version') ?? '1.0.0';
            $masterServer = config('updater.master_server');
            $siteCode = config('updater.site_code');

            if (!$masterServer) {
                return view('backend.update.index', ['data' => ['update' => false], 'currentVersion' => $currentVersion])->with('error', 'Master server URL is missing in config/updater.php configuration file!');
            }

            $licenseKey = $this->getValidLicenseKey();
            if (!$licenseKey) {
                return view('backend.update.index', ['data' => ['update' => false], 'currentVersion' => $currentVersion])->with('error', 'System is not activated. Please activate with a valid license key to receive updates.');
            }

            $clientDomain = request()->getHost();

            $response = Http::timeout(30)->post($masterServer . '/api/check-update', [
                'domain' => $clientDomain,
                'version' => $currentVersion,
                'license_key' => $licenseKey,
                'site_code' => $siteCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['update']) && $data['update'] == true) {
                    return view('backend.update.index', compact('data', 'currentVersion'));
                }
                
                return view('backend.update.index', ['data' => ['update' => false], 'currentVersion' => $currentVersion])->with('success', $data['message'] ?? 'You are using the latest version.');
            } else {
                return view('backend.update.index', ['data' => ['update' => false], 'currentVersion' => $currentVersion])->with('error', 'Master Server Error! Status Code: ' . $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Check Update Error: ' . $e->getMessage());
            return view('backend.update.index', ['data' => ['update' => false], 'currentVersion' => $currentVersion])->with('error', 'System Error: Unable to connect to the master server. Please try again later.');
        }
    }

    public function processUpdate(Request $request)
    {
        $downloadUrl = $request->input('download_url');
        $newVersion = $request->input('version'); 
        
        if (!$downloadUrl) {
            return redirect()->route('update.check')->with('error', 'Download URL not found!');
        }

        $licenseKey = $this->getValidLicenseKey();
        if (!$licenseKey) {
            return redirect()->route('update.check')->with('error', 'Unauthorized! Valid license key required to download updates.');
        }

        try {
            $zipFile = storage_path('app/update.zip');
            $clientDomain = request()->getHost();
            $siteCode = config('updater.site_code');

            $response = Http::timeout(300)->sink($zipFile)->post($downloadUrl, [
                'domain' => $clientDomain,
                'version' => $newVersion,
                'license_key' => $licenseKey,
                'site_code' => $siteCode,
            ]);
            
            if (!$response->successful()) {
                if (file_exists($zipFile)) { unlink($zipFile); } 
                return redirect()->route('update.check')->with('error', 'Failed to download the update file. Master Server rejected the request!');
            }

            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo(base_path());
                $zip->close();
                
                if (file_exists($zipFile)) {
                    unlink($zipFile);
                }

                Artisan::call('migrate', ['--force' => true]);
                
                if ($newVersion) {
                    $configPath = config_path('updater.php');
                    if (file_exists($configPath)) {
                        $configContents = file_get_contents($configPath);
                        $updatedContents = preg_replace(
                            "/'version'\s*=>\s*'.*?'/",
                            "'version' => '$newVersion'",
                            $configContents
                        );
                        file_put_contents($configPath, $updatedContents);
                    }
                }

                Artisan::call('optimize:clear');
                Artisan::call('config:clear');

                return redirect()->route('update.check')->with('success', "System updated successfully to version {$newVersion}!");
            } else {
                return redirect()->route('update.check')->with('error', 'Failed to extract the zip file! Please check storage permissions.');
            }

        } catch (\Exception $e) {
            Log::error('Process Update Error: ' . $e->getMessage());
            return redirect()->route('update.check')->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}