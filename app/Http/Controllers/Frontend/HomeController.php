<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSectionImage;
use App\Models\HomeCategory;
use App\Models\Slider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Type;
use App\Models\AboutUs;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Order; 

class HomeController extends Controller
{
    public function sendSMs(){
      
    }

    public function home(){
        $sliders = Slider::latest()->get();
        $brands = Type::whereNotNull('is_top')->take(12)->get();
        
        $cats = Category::whereNull('parent_id')->where('is_popular', 1)->with('subcats')->get();
        
        // ✅ আপডেট করা হয়েছে: all() এর বদলে first() এবং ভেরিয়েবলের নাম পরিবর্তন করা হয়েছে
        $featured_images = HomeSectionImage::first();
        
        $home_categories = HomeCategory::with('category')->orderBy('serial', 'asc')->paginate(10);
        $homeProducts = [];
        
        foreach ($home_categories as $hCats) {
            $hmProducts = Product::where('category_id', $hCats->category_id)
                        ->where('is_recommended', 1)
                        ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
                        ->latest()
                        ->get();
            $homeProducts[$hCats->category_id] = $hmProducts;
        }
        
        // ✅ আপডেট করা হয়েছে: 'images' এর বদলে 'featured_images' ভিউতে পাস করা হয়েছে
        return view('frontend.home', compact('sliders','cats','brands','featured_images','homeProducts'));
    }

    public function pageName($page){
        $page = Page::where('page', $page)->first();
        return view('frontend.about_us', compact('page'));
    }

    public function aboutUs(){
        $page = Page::where('page','about')->first();
        return view('frontend.about_us', compact('page'));
    }

    public function contactUs(){
        return view('frontend.contact_us');
    }
    
    public function privacyPolicy(){
        $page = Page::where('page','privacy-policy')->first();
        return view('frontend.privacy_policy', compact('page'));
    }
    
    public function termCondition(){
        $page = Page::where('page','term')->first();
        return view('frontend.term_and_condition', compact('page'));
    }
    
    public function faq(){
        return view('frontend.faq');
    }

    public function returnPolicy(){
        $page = Page::where('page','return-policy')->first();
        return view('frontend.return_policy', compact('page'));
    }

    public function contact(Request $request){
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required|numeric|digits:11|regex:/(01)[0-9]{9}/',
            'email' => '',
            'message' => 'required',
        ]);

        Contact::create($data);

        return response()->json(['success'=>true,'msg'=>'Successfully Created Your Info!']);
    }

    // ✅ আপডেট করা অর্ডার ট্র্যাকিং ফাংশন (With Order ID)
    public function orderTrack(Request $request){
        
        if ($request->ajax()) {
            $mobile = $request->mobile;
            $orders = Order::where('mobile', $mobile)
                            ->orderBy('id', 'desc')
                            ->get();

            if($orders->count() > 0){
                $html = '';
                foreach($orders as $order){
                    
                    $badgeClass = e(orderStatusBadgeClass($order->status));
                    $statusLabel = e(orderStatusLabel($order->status));

                    // HTML জেনারেশন
                    $html .= '
                    <div class="order-item p-3 mb-3 bg-white" style="border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <span class="text-muted d-block mb-1" style="font-size: 12px;"><i class="fas fa-file-invoice me-1" style="color: #2563eb;"></i> Invoice & Order ID</span>
                                
                                <span class="fw-bold" style="color: #1e293b; font-size: 15px;">#'.$order->invoice_no.'</span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size: 11px;">ID: '.$order->id.'</span>
                            
                            </div>
                            <div class="text-end">
                                <span class="text-muted d-block mb-1" style="font-size: 12px;"><i class="far fa-calendar-alt me-1" style="color: #2563eb;"></i> Date</span>
                                <span class="fw-bold text-dark" style="font-size: 13px;">'.$order->created_at->format('d M, Y').'</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fw-medium" style="font-size: 13px;"><i class="fas fa-money-bill-wave me-1 text-success"></i> Total Amount:</span>
                            <span class="fw-bold" style="color: #059669; font-size: 15px;">৳ '.number_format($order->final_amount).'</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="text-muted fw-medium" style="font-size: 13px;"><i class="fas fa-info-circle me-1 text-info"></i> Status:</span>
                            <span class="badge rounded-pill px-3 py-1 '.$badgeClass.'" style="font-size: 11px; font-weight: 600; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">'.$statusLabel.'</span>
                        </div>
                    </div>';
                }
                
                return response()->json(['status' => true, 'html' => $html]);
            } else {
                return response()->json(['status' => false]);
            }
        }

        return view('frontend.order_track');
    }
}
