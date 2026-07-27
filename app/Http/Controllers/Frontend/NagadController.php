<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Information;

class NagadController extends Controller
{
    private function getNagadConfig()
    {
        $info = Information::first();
        $isSandbox = $info->nagad_sandbox ?? 1;

        $merchantId = $isSandbox ? ($info->nagad_sandbox_merchant_id ?? '') : ($info->nagad_merchant_id ?? '');
        $merchantNumber = $isSandbox ? ($info->nagad_sandbox_merchant_number ?? '') : ($info->nagad_merchant_number ?? '');
        $publicKey = $isSandbox ? ($info->nagad_sandbox_public_key ?? '') : ($info->nagad_public_key ?? '');
        $privateKey = $isSandbox ? ($info->nagad_sandbox_private_key ?? '') : ($info->nagad_private_key ?? '');

        return [
            'base_url'        => $isSandbox ? 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs' : 'https://api.mynagad.com/api/dfs',
            'merchant_id'     => $merchantId,
            'merchant_number' => $merchantNumber,
            'public_key'      => $this->formatKey($publicKey, 'public'),
            'private_key'     => $this->formatKey($privateKey, 'private'),
            'is_active'       => $info->nagad_active ?? 0,
        ];
    }

    private function formatKey($key, $type)
    {
        if (empty($key)) return '';
        $header = $type == 'public' ? "-----BEGIN PUBLIC KEY-----\n" : "-----BEGIN RSA PRIVATE KEY-----\n";
        $footer = $type == 'public' ? "\n-----END PUBLIC KEY-----" : "\n-----END RSA PRIVATE KEY-----";
        
        if (!str_contains($key, 'BEGIN')) {
            $key = wordwrap($key, 64, "\n", true);
            return $header . $key . $footer;
        }
        return $key;
    }

    private function getClientIp()
    {
        return request()->ip() ?? '127.0.0.1';
    }

    public function pay($id)
    {
        $config = $this->getNagadConfig();
        
        if (!$config['is_active']) {
            return redirect()->route('front.checkout')->with("error", "Nagad Gateway is currently disabled.");
        }

        $order = Order::findOrFail($id);
        
        $DateTime = now()->timezone('Asia/Dhaka')->format('YmdHis');
        $InvoiceNo = $order->invoice_no;
        $merchantId = $config['merchant_id'];

        $SensitiveData = [
            'merchantId' => $merchantId,
            'datetime'   => $DateTime,
            'orderId'    => $InvoiceNo,
            'challenge'  => $this->generateRandomString()
        ];

        $PostData = [
            'accountNumber' => $config['merchant_number'],
            'dateTime'      => $DateTime,
            'sensitiveData' => $this->EncryptDataWithPublicKey(json_encode($SensitiveData)),
            'signature'     => $this->SignatureGenerate(json_encode($SensitiveData))
        ];

        $initializeUrl = $config['base_url'] . "/check-out/initialize/{$config['merchant_number']}/{$InvoiceNo}";
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-KM-IP-V4'   => $this->getClientIp(),
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-Client-Type' => 'PC_WEB'
        ])->post($initializeUrl, $PostData);

        if ($response->successful() && isset($response['sensitiveData'])) {
            $plainResponse = json_decode($this->DecryptDataWithPrivateKey($response['sensitiveData']), true);
            
            if (isset($plainResponse['paymentReferenceId']) && isset($plainResponse['challenge'])) {
                $paymentReferenceId = $plainResponse['paymentReferenceId'];
                
                $SensitiveDataOrder = [
                    'merchantId'   => $merchantId,
                    'orderId'      => $InvoiceNo,
                    'currencyCode' => '050',
                    'amount'       => number_format($order->final_amount, 2, '.', ''),
                    'challenge'    => $plainResponse['challenge']
                ];

                $PostDataOrder = [
                    'sensitiveData'       => $this->EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                    'signature'           => $this->SignatureGenerate(json_encode($SensitiveDataOrder)),
                    'merchantCallbackURL' => route('nagad.callback'),
                    'additionalMerchantInfo' => (object)[]
                ];

                $completeUrl = $config['base_url'] . "/check-out/complete/{$paymentReferenceId}";
                
                $orderResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-KM-IP-V4'   => $this->getClientIp(),
                    'X-KM-Api-Version' => 'v-0.2.0',
                    'X-KM-Client-Type' => 'PC_WEB'
                ])->post($completeUrl, $PostDataOrder);

                if ($orderResponse->successful() && isset($orderResponse['callBackUrl'])) {
                    return redirect()->away($orderResponse['callBackUrl']);
                }
            }
        }

        Log::error('Nagad Init Failed: ' . $response->body());
        return redirect()->route('front.checkout')->with("error", "Nagad Initialization Failed! Please check credentials.");
    }

    public function callback(Request $request)
    {
        $config = $this->getNagadConfig();
        $status = $request->status;
        $paymentRefId = $request->payment_ref_id;

        if ($status === 'Success' && $paymentRefId) {
            
            $verifyUrl = $config['base_url'] . "/verify/payment/{$paymentRefId}";
            
            $verifyResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-KM-IP-V4'   => $this->getClientIp(),
                'X-KM-Api-Version' => 'v-0.2.0',
                'X-KM-Client-Type' => 'PC_WEB'
            ])->get($verifyUrl);

            if ($verifyResponse->successful()) {
                $vData = $verifyResponse->json();
                
                if (isset($vData['status']) && $vData['status'] === 'Success') {
                    $order = Order::where('invoice_no', $vData['orderId'])->first();
                    
                    if ($order) {
                        $order->payment_status = 'Paid';
                        $order->status = 'Confirmed';
                        $order->transaction_id = $vData['paymentRefId'] ?? $request->payment_ref_id;
                        $order->save();
                        
                        OrderPayment::create([
                            'order_id'       => $order->id,
                            'payment_method' => 'Nagad PGW',
                            'amount'         => $vData['amount'] ?? $order->final_amount,
                            'transaction_id' => $vData['paymentRefId'] ?? $request->payment_ref_id,
                            'status'         => 'success',
                        ]);
                        
                        return redirect()->route('front.confirmOrderlanding', [$order->id]);
                    }
                }
            }
        }
        
        $order = Order::where('invoice_no', $request->order_id)->first();
        if ($order) {
            return redirect()->route('front.confirmOrderlanding', [$order->id])->with('error', 'Nagad Payment Failed or Canceled!');
        }

        return redirect()->route('front.home');
    }

    private function EncryptDataWithPublicKey($data)
    {
        $publicKey = $this->getNagadConfig()['public_key'];
        openssl_public_encrypt($data, $crypttext, $publicKey);
        return base64_encode($crypttext);
    }

    private function SignatureGenerate($data)
    {
        $privateKey = $this->getNagadConfig()['private_key'];
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function DecryptDataWithPrivateKey($crypttext)
    {
        $privateKey = $this->getNagadConfig()['private_key'];
        openssl_private_decrypt(base64_decode($crypttext), $plain_text, $privateKey);
        return $plain_text;
    }

    private function generateRandomString($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}