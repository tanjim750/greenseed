<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CouponCode;

class CouponCodeController extends Controller
{
    public function index()
    {
        $items = CouponCode::latest()->get();
        return view('backend.coupon_codes.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // যদি পারমিশন সেট করা থাকে তবে আন-কমেন্ট করুন
        /*
        if(!auth()->user()->can('coupon_codes.create')) {
            abort(403, 'unauthorized');
        }
        */
        return view('backend.coupon_codes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*
        if(!auth()->user()->can('coupon_codes.create')) {
            abort(403, 'unauthorized');
        }
        */

        $data = $request->validate([
            'code'           => 'required|unique:coupon_codes,code',
            'amount'         => 'required',
            'start'          => 'required',
            'end'            => 'required',
            'minimum_amount' => 'nullable',
            'discount_type'  => 'required',
        ]);

        CouponCode::create($data);

        return response()->json([
            'status' => true,
            'msg'    => 'CouponCode Is Created !!',
            'url'    => route('admin.coupon_codes.index')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        /*
        if(!auth()->user()->can('coupon_codes.edit')) {
            abort(403, 'unauthorized');
        }
        */

        $item = CouponCode::find($id);
        return view('backend.coupon_codes.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        /*
        if(!auth()->user()->can('coupon_codes.edit')) {
            abort(403, 'unauthorized');
        }
        */

        $coupon = CouponCode::find($id); // Fixed variable name from $category to $coupon
        
        $data = $request->validate([
            'code'           => 'required|unique:coupon_codes,code,'.$id,
            'amount'         => 'required',
            'start'          => 'required',
            'end'            => 'required',
            'minimum_amount' => 'nullable',
            'discount_type'  => 'required',
        ]);
       
        $coupon->update($data);

        return response()->json([
            'status' => true,
            'msg'    => 'CouponCode Is Updated !!',
            'url'    => route('admin.coupon_codes.index')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        /*
        if(!auth()->user()->can('coupon_codes.delete')) {
            abort(403, 'unauthorized');
        }
        */

        $coupon = CouponCode::find($id); // Fixed variable name
        $coupon->delete();
        
        return response()->json([
            'status' => true, 
            'msg'    => 'CouponCode Is Deleted !!'
        ]);
    }
}