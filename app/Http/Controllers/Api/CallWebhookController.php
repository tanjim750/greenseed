<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Information;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CallWebhookController extends Controller
{
    public function handleManyDialWebhook(Request $request)
    {
        try {
            $payload = $request->input('callPayload'); 
            $userPressed = $request->input('userPressed'); 
            $status = $request->input('status'); 

            if (!$payload) {
                return response()->json(['error' => 'Invalid Payload'], 400);
            }

            $orderId = str_replace('Order-', '', $payload);
            $order = Order::find($orderId);

            if ($order) {
                $old_status = strtolower($order->status);

                if ($status === 'ANSWER') {
                    if ($userPressed == '1') {
                        $order->status = 'Confirmed';
                        $order->save();
                        
                        if (function_exists('logActivity')) {
                            logActivity('Auto Call', 'Order', "Customer pressed 1. Order auto-confirmed via ManyDial.", $order->id, ['status' => $old_status], ['status' => 'Confirmed']);
                        }

                        $this->sendSMS($order, 'sms_confirmed_active', 'sms_confirmed');

                    } elseif ($userPressed == '2') {
                        $order->status = 'Cancelled';
                        $order->save();
                        
                        if (function_exists('logActivity')) {
                            logActivity('Auto Call', 'Order', "Customer pressed 2. Order auto-cancelled via ManyDial.", $order->id, ['status' => $old_status], ['status' => 'Cancelled']);
                        }
                    } else {
                        if (function_exists('logActivity')) {
                            logActivity('Auto Call', 'Order', "Customer answered but did not press any button.", $order->id);
                        }
                    }
                } else {
                    if ($order->call_attempt < 2) {
                        \App\Jobs\SendOrderConfirmationCall::dispatch($order)->delay(now()->addMinutes(10));
                        
                        if (function_exists('logActivity')) {
                            logActivity('Auto Call Retry', 'Order', "Call Status: {$status}. Scheduling retry 2 in 10 minutes.", $order->id);
                        }
                    } else {
                        $order->status = 'On Hold';
                        $order->save();
                        
                        if (function_exists('logActivity')) {
                            logActivity('Auto Call Failed', 'Order', "Call failed twice. Status: {$status}. Order moved to On Hold.", $order->id, ['status' => $old_status], ['status' => 'On Hold']);
                        }

                        $this->sendSMS($order, 'sms_on_hold_active', 'sms_on_hold');
                    }
                }
            }

            return response()->json(['status' => 'Webhook received and processed successfully'], 200);

        } catch (\Exception $e) {
            Log::error('ManyDial Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function sendSMS($order, $activeCol, $tempCol)
    {
        $settings = Information::first();

        if ($settings && $settings->sms_api_key && $order->mobile) {
            if ($settings->$activeCol == 1 && !empty($settings->$tempCol)) {
                
                $msg = str_replace(
                    ['{order_id}', '{amount}', '{status}'], 
                    [$order->id, $order->final_amount, ucfirst($order->status)], 
                    $settings->$tempCol
                );
                
                try {
                    Http::get("http://bulksmsbd.net/api/smsapi", [
                        'api_key'  => $settings->sms_api_key,
                        'type'     => 'text',
                        'number'   => $order->mobile,
                        'senderid' => $settings->sms_sender_id,
                        'message'  => $msg,
                    ]);
                } catch (\Exception $e) {
                    Log::error('ManyDial Auto SMS Failed: ' . $e->getMessage());
                }
            }
        }
    }
}