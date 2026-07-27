<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class FacebookConversionService
{
    private Client $client;
    private $pixelId;
    private $accessToken;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://graph.facebook.com/v19.0/',
            'timeout'  => 10.0,
            'verify'   => true,
        ]);

        $this->pixelId = $this->getConfig('fb_pixel_id', 'FACEBOOK_PIXEL_ID');
        $this->accessToken = $this->getConfig('fb_access_token', 'FACEBOOK_ACCESS_TOKEN');
    }

    private function getConfig($settingKey, $envKey)
    {
        if (function_exists('setting') && setting($settingKey)) {
            return setting($settingKey);
        }
        return config("services.facebook.{$settingKey}") ?? env($envKey);
    }

    public function sendEvent(string $eventName, array $customData = [], ?string $eventId = null, array $override = []): array 
    {
        try {
            if (empty($this->pixelId) || empty($this->accessToken)) {
                Log::warning('Facebook CAPI skipped: missing ID or Token');
                return ['ok' => false, 'error' => 'Missing Configuration'];
            }

            $userData = $this->getUserData();

            if (!empty($override['user_data']) && is_array($override['user_data'])) {
                $userData = array_merge($userData, $override['user_data']);
            }

            $userData = $this->cleanArray($userData);

            $eventData = [
                'event_name'       => $eventName,
                'event_time'       => $override['event_time'] ?? time(),
                'event_source_url' => $override['event_source_url'] ?? request()->fullUrl(),
                'action_source'    => $override['action_source'] ?? 'website',
                'user_data'        => $userData,
            ];

            if (!empty($eventId)) {
                $eventData['event_id'] = $eventId;
            }

            if (!empty($customData)) {
                $eventData['custom_data'] = $customData;
            }

            $payload = ['data' => [$eventData]];

            $testCode = $this->getConfig('fb_pixel_test_code', 'FACEBOOK_PIXEL_TEST_CODE');
            if ($testCode) {
                $payload['test_event_code'] = $testCode;
            }

            $response = $this->client->post($this->pixelId . '/events', [
                'query' => ['access_token' => $this->accessToken],
                'json'  => $payload,
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            return ['ok' => true, 'status' => $response->getStatusCode(), 'body' => json_decode($response->getBody()->getContents(), true)];

        } catch (\Throwable $e) {
            Log::error('Facebook CAPI Error [' . $eventName . ']: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendAddToCart(array $data, ?string $eventId = null): array
    {
        $customData = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float)($data['value'] ?? 0),
            'content_ids'  => $data['content_ids'] ?? [],
            'content_type' => 'product',
            'contents'     => $data['contents'] ?? [], 
        ];
        return $this->sendEvent('AddToCart', $customData, $eventId);
    }

    public function sendViewContent(array $data, ?string $eventId = null): array
    {
        $customData = [
            'currency'         => $data['currency'] ?? 'BDT',
            'value'            => (float)($data['value'] ?? 0),
            'content_ids'      => [(string)($data['product_id'] ?? '')],
            'content_name'     => $data['product_name'] ?? '',
            'content_type'     => 'product',
            'content_category' => $data['content_category'] ?? null,
        ];
        return $this->sendEvent('ViewContent', $customData, $eventId);
    }

    public function sendInitiateCheckout(array $data, ?string $eventId = null): array
    {
        $customData = [
            'currency'     => $data['currency'] ?? 'BDT',
            'value'        => (float)($data['value'] ?? 0),
            'num_items'    => (int)($data['num_items'] ?? 1),
            'content_ids'  => $data['content_ids'] ?? [],
            'contents'     => $data['contents'] ?? [],
            'content_type' => 'product',
        ];
        return $this->sendEvent('InitiateCheckout', $customData, $eventId);
    }

    public function sendPurchase(array $data, ?string $eventId = null, array $userData = [], ?string $eventUrl = null): array
    {
        $override = ['user_data' => $userData];
        
        if (!empty($eventUrl)) {
            $override['event_source_url'] = $eventUrl;
        }

        return $this->sendEvent('Purchase', $data, $eventId, $override);
    }

    private function getUserData(): array
    {
        $request = request();
        $data = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
        ];

        if (!empty($_COOKIE['_fbc'])) $data['fbc'] = $_COOKIE['_fbc'];
        if (!empty($_COOKIE['_fbp'])) $data['fbp'] = $_COOKIE['_fbp'];

        $user = Auth::user();
        if ($user) {
            if (!empty($user->email)) {
                $data['em'] = [$this->hash($user->email)];
            }
            if (!empty($user->phone) || !empty($user->phone_number)) {
                $phone = $user->phone ?? $user->phone_number;
                $data['ph'] = [$this->hashPhone($phone)];
            }
            if (!empty($user->name)) {
                $parts = explode(' ', $user->name, 2);
                $data['fn'] = [$this->hash($parts[0])];
                if (isset($parts[1])) {
                    $data['ln'] = [$this->hash($parts[1])];
                }
            }
        }
        return $data;
    }

    public function hash(?string $value): ?string
    {
        $value = trim(strtolower((string)$value));
        if ($value === '') return null;
        return hash('sha256', $value);
    }

    public function hashPhone(?string $value): ?string
    {
        $value = preg_replace('/\D+/', '', (string)$value);
        if ($value === '') return null;
        
        if (strlen($value) == 11 && str_starts_with($value, '0')) {
            $value = '88' . $value;
        }
        return hash('sha256', $value);
    }

    private function cleanArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $v = array_values(array_filter($v, fn($x) => $x !== null && $x !== ''));
                if (!empty($v)) $out[$k] = $v;
            } else {
                if ($v !== null && $v !== '') $out[$k] = $v;
            }
        }
        return $out;
    }
}