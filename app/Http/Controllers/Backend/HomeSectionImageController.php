<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSectionImage;

class HomeSectionImageController extends Controller
{
    public function index()
    {
        $items=HomeSectionImage::paginate(20);
        return view('backend.home_section_images.index', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('image.create'))
        {
            abort(403, 'unauthorized');
        }

        $request->validate([
             'section'=> 'required'
        ]);

        $homeimage = new HomeSectionImage();
        
        // ✅ ফিক্স: section এর ভ্যালু 'none' হলে ডাটাবেজে null বা 0 সেভ হবে
        $homeimage->section = ($request->section == 'none') ? null : $request->section;
        
        $homeimage->link = $request->link;
        $homeimage->is_for_small = $request->is_for_small;
        
        // নতুন লিংকের ডাটা সেভ
        $homeimage->left_link_1 = $request->left_link_1;
        $homeimage->left_link_2 = $request->left_link_2;
        $homeimage->left_link_3 = $request->left_link_3;
        $homeimage->left_link_4 = $request->left_link_4;
        $homeimage->right_link  = $request->right_link;

        if($request->hasFile('mobile_image')) {
            $originName = $request->file('mobile_image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('mobile_image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('mobile_image')->move(public_path('homeimages'), $fileName);
            $homeimage->mobile_image = $fileName;
        }
        
        if($request->hasFile('image')) {
            $originName = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('image')->move(public_path('homeimages'), $fileName);
            $homeimage->image = $fileName;
        }

        // ✅ নতুন ৫টি ইমেজ আপলোডের লজিক
        $newImageFields = ['left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image'];
        
        foreach ($newImageFields as $field) {
            if ($request->hasFile($field)) {
                $originName = $request->file($field)->getClientOriginalName();
                $fileName = pathinfo($originName, PATHINFO_FILENAME);
                $extension = $request->file($field)->getClientOriginalExtension();
                $fileName = $fileName . time() . '.' . $extension;
            
                $request->file($field)->move(public_path('homeimages'), $fileName);
                $homeimage->$field = $fileName;
            }
        }

        $homeimage->save();

        return response()->json(['status'=>true ,'msg'=>'HomeSectionImage Is Created !!','url'=>route('admin.home_section_images.index')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!auth()->user()->can('image.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=HomeSectionImage::find($id);
        return view('backend.home_section_images.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // 🔴 ডিবাগিং চেক: ফর্ম সাবমিট করলে এই লাইনটি ডাটাগুলো স্ক্রিনে দেখাবে (প্রয়োজন না হলে এটি মুছে দিন)
        // dd($request->all());

        if(!auth()->user()->can('image.edit'))
        {
            abort(403, 'unauthorized');
        }

        $homeimage = HomeSectionImage::find($id);
        
        $request->validate([
             'section'=> 'required'
        ]);

        // বেসিক ডাটা আপডেট
        $homeimage->title = $request->title ?? $homeimage->title;
        $homeimage->text = $request->text ?? $homeimage->text;
        $homeimage->link = $request->link ?? $homeimage->link;
        
        // ✅ ফিক্স: section এর ভ্যালু 'none' হলে ডাটাবেজে null বা 0 সেভ হবে
        $newSectionValue = ($request->section == 'none') ? null : $request->section;
        $homeimage->section = $newSectionValue ?? $homeimage->section;
        
        $homeimage->is_for_small = $request->has('is_for_small') ? $request->is_for_small : $homeimage->is_for_small;

        // নতুন লিংক আপডেট
        $homeimage->left_link_1 = $request->left_link_1 ?? $homeimage->left_link_1;
        $homeimage->left_link_2 = $request->left_link_2 ?? $homeimage->left_link_2;
        $homeimage->left_link_3 = $request->left_link_3 ?? $homeimage->left_link_3;
        $homeimage->left_link_4 = $request->left_link_4 ?? $homeimage->left_link_4;
        $homeimage->right_link  = $request->right_link ?? $homeimage->right_link;

        if($request->hasFile('image')) {
            if (!empty($homeimage->image)) {
                deleteImage('homeimages', $homeimage->image);
            }
            $originName = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('image')->move(public_path('homeimages'), $fileName);
            $homeimage->image = $fileName;
        }
        
        if($request->hasFile('mobile_image')) {
            if (!empty($homeimage->mobile_image)) {
                deleteImage('homeimages', $homeimage->mobile_image);
            }
            $originName = $request->file('mobile_image')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('mobile_image')->getClientOriginalExtension();
            $fileName =$fileName.time().'.'.$extension;
        
            $request->file('mobile_image')->move(public_path('homeimages'), $fileName);
            $homeimage->mobile_image = $fileName;
        }

        // ✅ নতুন ৫টি ইমেজ আপডেট এবং আগেরটা ডিলিট করার লজিক
        $newImageFields = ['left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image'];
        
        foreach ($newImageFields as $field) {
            if ($request->hasFile($field)) {
                // আগের ইমেজ থাকলে সেটা ডিলিট করবে
                if (!empty($homeimage->$field)) {
                    deleteImage('homeimages', $homeimage->$field);
                }

                $originName = $request->file($field)->getClientOriginalName();
                $fileName = pathinfo($originName, PATHINFO_FILENAME);
                $extension = $request->file($field)->getClientOriginalExtension();
                $fileName = $fileName . time() . '.' . $extension;
            
                $request->file($field)->move(public_path('homeimages'), $fileName);
                $homeimage->$field = $fileName;
            }
        }
        
        $homeimage->save();

        return response()->json(['status'=>true ,'msg'=>'HomeSectionImage Is Updated !!','url'=>route('admin.home_section_images.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('image.delete'))
        {
            abort(403, 'unauthorized');
        }

        $item=HomeSectionImage::find($id);
        
        deleteImage('homeimages', $item->image);
        deleteImage('homeimages', $item->mobile_image);

        // ✅ নতুন ৫টি ইমেজ ডিলিট করার লজিক
        $newImageFields = ['left_image_1', 'left_image_2', 'left_image_3', 'left_image_4', 'right_image'];
        foreach ($newImageFields as $field) {
            if (!empty($item->$field)) {
                deleteImage('homeimages', $item->$field);
            }
        }

        $item->delete();
        return response()->json(['status'=>true ,'msg'=>'HomeSectionImage Is Deleted !!']);

    }
}