<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Information;

class SendOrderConfirmationCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        $this->order->increment('call_attempt');

        try {
            $info = Information::first();
            
            if (!$info || empty($info->manydial_api_key) || empty($info->manydial_caller_id)) {
                Log::warning('ManyDial credentials are missing in settings.');
                return;
            }

            $messages = [
                'welcome' => "হ্যালো, আপনার অর্ডারটি সফলভাবে প্লেস হয়েছে। কনফার্ম করতে ১ চাপুন। বাতিল করতে ২ চাপুন।",
                'menuMessage1' => "ধন্যবাদ, আপনার অর্ডারটি কনফার্ম করা হয়েছে।",
                'menuMessage2' => "আপনার অর্ডারটি বাতিল করা হয়েছে।"
            ];

            $buttons = [
                ['id' => 'menuMessage1', 'key' => '1', 'value' => 'Confirm Order'],
                ['id' => 'menuMessage2', 'key' => '2', 'value' => 'Cancel Order']
            ];

            $phone = $this->order->mobile ?? $this->order->phone;

            $response = Http::withHeaders([
                'x-api-key' => $info->manydial_api_key
            ])->asMultipart()->post('https://api.manydial.com/v1/portal/call/dispatch', [
                ['name' => 'callPayload', 'contents' => 'Order-' . $this->order->id],
                ['name' => 'callerId', 'contents' => $info->manydial_caller_id],
                ['name' => 'number', 'contents' => $phone],
                ['name' => 'messages', 'contents' => json_encode($messages, JSON_UNESCAPED_UNICODE)],
                ['name' => 'buttons', 'contents' => json_encode($buttons)],
                ['name' => 'deliveryHook', 'contents' => url('/api/webhook/manydial')]
            ]);

            if ($response->successful()) {
                Log::info('ManyDial call dispatched successfully for Order ID: ' . $this->order->id);
            } else {
                Log::error('ManyDial call dispatch failed for Order ID: ' . $this->order->id . ' Response: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Call Dispatch Exception: ' . $e->getMessage());
        }
    }
}