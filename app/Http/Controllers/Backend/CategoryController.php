<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PopularCategory;
use App\Models\HomeCategory;
use App\Models\Category;
use App\Models\Type;
use DB;
use Image;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
    public function index()
    {
        $types=Type::all();
        $items=Category::latest()->paginate(20);
        $cats=Category::whereNull('parent_id')->select('name','id')->pluck('name','id')->toArray();
        return view('backend.categories.index', compact('items','types','cats'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->can('category.create'))
        {
            abort(403, 'unauthorized');
        }
        $data=$request->validate([
             'name'=> 'required',
          	 //'url'=> 'required|unique:categories,url',
             'parent_id'=> '',
        ]);
        // Generate initial slug
        $slug = Str::slug($data['name']);
        $originalSlug = $slug;
        $counter = 1;
    
        // Check if the slug already exists and make it unique
        while (Category::where('url', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    
        $data['url'] = $slug;
        
        if($request->hasFile('image')) {
            $image = Image::make($request->file('image'));
  
            /**
             * Main Image Upload on Folder Code
             */
            $imageName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('categories/');
            $image->resize(90,90);
            $image->save($destinationPath.$imageName);
            $data['image']=$imageName;
        }

        Category::create($data);

        return response()->json(['status'=>true ,'msg'=>'Category Is  Created !!','url'=>route('admin.categories.index')]);
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
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $item=Category::find($id);
        $types=Type::all();
        $cats=Category::whereNull('parent_id')->select('name','id')->pluck('name','id')->toArray();
        return view('backend.categories.edit', compact('item','types','cats'));
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
        if(!auth()->user()->can('category.edit'))
        {
            abort(403, 'unauthorized');
        }

        $category=Category::find($id);
        $data=$request->validate([
             'name'=> 'required',
          	 'url'=> 'required|unique:categories,url,'.$id,
             'parent_id'=> '',
        ]);

        if($request->hasFile('image')) {
            deleteImage('categories', $category->image);
            
            $image = Image::make($request->file('image'));
            /**
             * Main Image Upload on Folder Code
             */
            $imageName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('categories/');
            $image->resize(90,90);
            $image->save($destinationPath.$imageName);
            $data['image']=$imageName;
        }
       
        $category->update($data);

        return response()->json(['status'=>true ,'msg'=>'Category Is Updated !!','url'=>route('admin.categories.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!auth()->user()->can('category.delete'))
        {
            abort(403, 'unauthorized');
        }

        $category=Category::find($id);
        deleteImage('categories', $category->image);
        $home_cat = HomeCategory::where('category_id', $id)->first();
        if($home_cat){
            $home_cat->delete();
        }
        $category->delete();
        return response()->json(['status'=>true ,'msg'=>'Category Is Deleted !!']);

    }
    
    public function homepage_categories(Request $request) {
        $popular_categories = PopularCategory::with('category')->latest()->get();
       
        $categories = Category::where('parent_id', null)->get();
        return view('backend.popular_categories.index', compact('popular_categories','categories'));
    }
    
    public function homeCatgeory() {
        $all_categories = Category::where('parent_id', null)->get();
        $home_categories = HomeCategory::with('category')->latest()->paginate(10);
        return view('backend.home_categories.index', compact('all_categories','home_categories'));
    }
    
    public function storehomeCatgeory(Request $request) {
        
        $data=$request->validate([
             'category_id'=> '',
          	 'serial'=> ''
        ]);
        
        HomeCategory::create($data);
        return response()->json(['status'=>true ,'msg'=>'Home Category Is  Created !!','url'=>route('admin.homecat')]);
    }
    
    public function delhomeCatgeory(Request $request, $id) {
      $delete_data = HomeCategory::find($id);
      $delete_data->delete();
      return response()->json(['status'=>true ,'msg'=>'Home Category Is Deleted !!']);
    }
    
    public function popularCatgeory(Request $request)
    {
        $catIds = $request->cat_ids;
        $updateData = [];
    
        // Dynamically set the update field based on query
        if ($request->has('is_popular')) {
            $updateData['is_popular'] = $request->is_popular == 1 ? 1 : 0;
        }
    
        if ($request->has('is_menu')) {
            $updateData['is_menu'] = $request->is_menu == 1 ? 1 : 0;
        }
    
        // If nothing to update, return error
        if (empty($updateData)) {
            return response()->json(['status' => false, 'msg' => 'No valid action found.']);
        }
    
        // Perform update
        DB::table('categories')
            ->whereIn('id', $catIds)
            ->update($updateData);
    
        return response()->json(['status' => true, 'msg' => 'Category status updated successfully!']);
    }


}
