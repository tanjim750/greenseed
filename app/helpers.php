<?php

function setting($key = null) {
    $info = App\Models\Information::first();
    return $key ? $info->$key ?? null : $info;
}

function dateFormate($date=null){
    $value='';
    if ($date) {
        $value=date('M d Y', strtotime($date));
    }
    return $value;
}

function getImage($folder=null,$value=null){
    $url = asset('images/no_found.png');
    $path = public_path($folder.'/'.$value);
    if (!empty($folder) && (!empty($value))) {
        if(file_exists($path)){
            $url = asset($folder.'/'.$value);
        }
    }
    return $url;
}

function deleteImage($folder=null, $file=null){
    if (!empty($folder) && !empty($file)) {
        $path = public_path($folder.'/'.$file);
        $isExists = file_exists($path);
        if ($isExists) {
            unlink($path);
        }
    }
    return true;
}

function priceFormate($amount=0){
    return '৳'.number_format($amount,0);
}

function getRole(){
    return auth()->user()->roles->pluck('name')[0] ??'';
}

function getTotalAmount(){
    $cart = session()->get('cart', []);
    $total = 0;
    foreach($cart as $cartItem){
        $total += $cartItem['price'] * $cartItem['quantity'];
    }
    return $total;
}

function getTotalCart(){
    return count(session()->get('cart',[]));
}

function getProductInfo($product){
    $price=($product->after_discount  > 0) ? $product->after_discount : $product->sell_price;
    $discount_amount=$product->dicount_amount;
    
    $old_price=$product->sell_price;

    return ['price'=>$price,'discount_amount'=>$discount_amount,'old_price'=>$old_price];
}

function getSectionLists(){
    return ['0'=>'None','1'=>'Trending','2'=>'Hot Deals','3'=>'Recommended','4'=>'Top Brand'];
}

function getOrderStatus($type=""){
    return \App\Models\OrderStatus::activeOptions(true);
}

function orderStatusLabel($status=null){
    return \App\Models\OrderStatus::labelFor($status);
}

function orderStatusBadgeClass($status=null){
    return \App\Models\OrderStatus::badgeClassFor($status);
}

function orderStatusesForFlag($flag){
    return \App\Models\OrderStatus::namesForFlag($flag);
}

function getPaymentStatus(){
    return [
        'Unpaid'   => 'Unpaid',
        'Partial'  => 'Partial',
        'Paid'     => 'Paid',
        'Refunded' => 'Refunded'
    ];
}

function getOrderMethod(){
    return ['cash'=>'Cash','Card'=>'Card'];
}

function SendSms($number=null,$message=null){
    $data = [
            'user' => 'sahaalfash',
            'pwd' => '66pueu99',
            'senderid' => '8809617611152', 
            'CountryCode' => '+880',
            'mobileno' => $number,   
            'msgtext' => $message
    ];
    $query = http_build_query($data);
    $url = "http://mshastra.com/sendurl.aspx?$query";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl_scraped_page =  curl_exec($ch);
    curl_close($ch);
    return $curl_scraped_page;
}

function getPageName(){
    return ['about-us'=>'About Us','return-policy'=>'Return Policy','privacy-policy'=>'Privacy Policy','terms-condition'=>'Term And Condition'];
}

function getCouponDiscount(){
    $coupon=session()->get('coupon_discount');
    $type=session()->get('discount_type');
  
    $cart = session()->get('cart');
    $total=0;
    $amount=0;
    
    if($cart){
        foreach($cart as $id=>$item){
            $total +=$item['price'] * $item['quantity'];
        }
    }
  
    if($type=='fixed'){
        $amount=$coupon;
    }else{
        $amount=(($total*$coupon)/ 100);
    }
    
    if(($total >0) and ($coupon)){
        $amount=$amount;
    }
    
    return round($amount);
}

function full_name($user)
{
    if($user)
    {
        return $user->first_name.' '.$user->last_name;
    }
    
    return '';
}

function BanglaText($index)
{      
  $bangla_text = array(
    "cust_info"             =>"কাস্টমার ইনফরমেশন",
    "offer"                 => "মেগা অফার",
    'tk'                    => "টাকা",
    "do_order"              => "অর্ডার করতে ক্লিক করুন",
    "instruction"           =>"অর্ডার কনফার্ম করতে আপনার নাম, ঠিকানা, মোবাইল নাম্বার লিখে অর্ডার কনফার্ম করুন বাটনে ক্লিক করুন",
    "name"                  => "আপনার নাম",
    "placeholder_name"      => "আপনার নাম লিখুন",
    "mobile"                => "আপনার মোবাইল নাম্বার",
    "placeholder_mobile"    => "আপনার  মোবাইল নাম্বার লিখুন",
    "address"               => "আপনার সম্পূর্ন ঠিকানা",
    "placeholder_address"   => "",
    "delivery_zone"         => "ডেলিভারি এলাকা নির্বাচন করুন",
    "confirm_order"         => "অর্ডার কনফার্ম করুন",
    "alert"                 => "অনুগ্রহ করে পার্সেল রিসিভ করার ব্যাপারে ১০০% নিশ্চিত হয়েই অর্ডার করুন। আপনার সহযোগিতা আমাদের ডেলিভারি প্রক্রিয়াকে আরও নির্ভুল ও দ্রুত করতে সাহায্য করবে।",
    "order_information"     => "অর্ডার ইনফরমেশন",
    "order"                 => "অর্ডার করুন",
    "land_order"            => "অর্ডার করতে চাই",
    "cart"                  => "কার্টে যোগ করুন",
    "land_instruction"      => "অর্ডার করতে নিচের ফর্মটি সঠিক তথ্য দিয়ে পূরন করুন",
    "order_ensure"          => "১০০% শিউর হয়ে অর্ডার করুন" 
    );
  return $bangla_text[$index]; 
}

function logActivity($action, $module, $description, $order_id = null, $old_data = null, $new_data = null) {
    try {
        if (!\Illuminate\Support\Facades\Cache::has('daily_log_cleanup')) {
            \App\Models\ActivityLog::where('created_at', '<', now()->subDays(10))->delete();
            \Illuminate\Support\Facades\Cache::put('daily_log_cleanup', true, now()->addHours(24));
        }

        \App\Models\ActivityLog::create([
            'user_id'     => auth()->check() ? auth()->id() : null,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'order_id'    => $order_id,
            'old_data'    => $old_data,
            'new_data'    => $new_data,
            'url'         => request()->fullUrl(),
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    } catch (\Exception $e) {
    }
}
