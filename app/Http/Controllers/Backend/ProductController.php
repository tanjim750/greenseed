<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\Type;
use App\Models\Color;
use App\Models\Variation;
use DB;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;
use Image;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $items = Product::with([
                'variations.size',
                'variations.color',
                'variations.stocks'
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->limit(20)
            ->get();

        $data = $items->map(function ($p) {
            $imageUrl = function_exists('getImage')
                ? getImage('products', $p->image)
                : asset('products/' . $p->image);

            $basePrice = 0.0;
            foreach ([$p->after_discount, $p->sell_price, $p->regular_price] as $cand) {
                $n = is_null($cand) ? null : (float) $cand;
                if (!is_null($n) && $n > 0) {
                    $basePrice = $n;
                    break;
                }
            }

            $vars = $p->variations->map(function ($v) {
                $best = 0.0;
                foreach ([$v->after_discount_price, $v->discount_price ?? null, $v->price] as $cand) {
                    $n = is_null($cand) ? null : (float) $cand;
                    if (!is_null($n) && $n > 0) {
                        $best = $n;
                        break;
                    }
                }

                return [
                    'id' => (int) $v->id,
                    'size_id' => (int) ($v->size_id ?? 0),
                    'size_name' => $v->size->name ?? ($v->size_label ?? $v->size ?? ''),
                    'color_id' => (int) ($v->color_id ?? 0),
                    'color_name' => $v->color->name ?? ($v->color_label ?? $v->color ?? ''),
                    'price' => (float) $best,
                    'raw' => (float) ($v->price ?? 0),
                    'stock' => (int) ($v->stocks->sum('quantity') ?? 0),
                    'image' => $v->image ? asset('products/' . $v->image) : null, 
                ];
            })->values();

            $sizes = $vars->filter(fn($v) => !empty($v['size_id']))
                ->map(fn($v) => ['id' => $v['size_id'], 'name' => $v['size_name']])
                ->unique('id')
                ->values();

            $colors = $vars->filter(fn($v) => !empty($v['color_id']))
                ->map(fn($v) => ['id' => $v['color_id'], 'name' => $v['color_name']])
                ->unique('id')
                ->values();

            $matrix = [];
            foreach ($vars as $v) {
                $key = $v['size_id'] . '_' . $v['color_id'];
                $matrix[$key] = [
                    'variation_id' => $v['id'],
                    'price' => $v['price'],
                    'raw' => $v['raw'],
                    'stock' => $v['stock'],
                    'image' => $v['image'], 
                ];
            }

            return [
                'id' => (int) $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'image' => $imageUrl,
                'price' => (float) $basePrice,
                'sell_price' => (float) ($p->sell_price ?? 0),
                'regular_price' => (float) ($p->regular_price ?? 0),
                'after_discount_price' => (float) ($p->after_discount ?? 0),
                'variations' => $vars,
                'sizes' => $sizes,
                'colors' => $colors,
                'matrix' => $matrix,
            ];
        })->values();

        return response()->json($data);
    }

    public function variationMatrix($productId)
    {
        $p = Product::with([
                'variations.size',
                'variations.color',
                'variations.stocks'
            ])->findOrFail($productId);

        $vars = $p->variations->map(function ($v) {
            $best = 0.0;
            foreach ([$v->after_discount_price, $v->discount_price ?? null, $v->price] as $cand) {
                $n = is_null($cand) ? null : (float) $cand;
                if (!is_null($n) && $n > 0) {
                    $best = $n;
                    break;
                }
            }

            return [
                'id' => (int) $v->id,
                'size_id' => (int) ($v->size_id ?? 0),
                'size_name' => $v->size->name ?? ($v->size_label ?? $v->size ?? ''),
                'color_id' => (int) ($v->color_id ?? 0),
                'color_name' => $v->color->name ?? ($v->color_label ?? $v->color ?? ''),
                'price' => (float) $best,
                'raw' => (float) ($v->price ?? 0),
                'stock' => (int) ($v->stocks->sum('quantity') ?? 0),
                'image' => $v->image ? asset('products/' . $v->image) : null, 
            ];
        })->values();

        $sizes = $vars->filter(fn($v) => !empty($v['size_id']))
            ->map(fn($v) => ['id' => $v['size_id'], 'name' => $v['size_name']])
            ->unique('id')
            ->values();

        $colors = $vars->filter(fn($v) => !empty($v['color_id']))
            ->map(fn($v) => ['id' => $v['color_id'], 'name' => $v['color_name']])
            ->unique('id')
            ->values();

        $matrix = [];
        foreach ($vars as $v) {
            $key = $v['size_id'] . '_' . $v['color_id'];
            $matrix[$key] = [
                'variation_id' => $v['id'],
                'price' => $v['price'],
                'raw' => $v['raw'],
                'stock' => $v['stock'],
                'image' => $v['image'], 
            ];
        }

        return response()->json([
            'product_id' => (int) $p->id,
            'sizes' => $sizes,
            'colors' => $colors,
            'variations' => $vars,
            'matrix' => $matrix,
        ]);
    }

    public function productExport()
    {
        return Excel::download(new ProductExport, 'products.xlsx');
    }

    public function index()
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'unauthorized');
        }

        $cat_id = '';
        $q = request()->q;
        $query = Product::query();

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('name', 'Like', '%' . $q . '%')
                    ->orwhere('description', 'Like', '%' . $q . '%')
                    ->orwhere('sku', 'Like', '%' . $q . '%');
            });
        }

        if (auth()->user()->hasRole('admin') == false) {
            $query->where('user_id', auth()->user()->id);
        }

        $categories = Category::where('parent_id', null)->get();
        $items = $query->latest()->paginate(30);

        return view('backend.products.index', compact('items', 'q', 'categories', 'cat_id'));
    }

    public function getSubcategory()
    {
        $cats = Category::where('parent_id', request('cat_id'))
            ->select('name', 'id')->pluck('name', 'id')->toArray();

        return response()->json($cats);
    }

    public function create()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'unauthorized');
        }

        $cats = Category::whereNull('parent_id')->get();
        $sizes = Size::all();
        $types = Type::all();
        $colors = Color::all();
        return view('backend.products.create', compact('cats', 'sizes', 'types', 'colors'));
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '512M'); 

        if (!auth()->user()->can('product.create')) {
            abort(403, 'unauthorized');
        }

        $data = $request->validate([
            'name' => 'required',
            'type' => 'required|in:single,variable',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', 
            'category_id' => 'required',
            'sub_category_id' => 'nullable',
            'type_id' => 'nullable',
            'short_description' => 'nullable',
            'description' => 'nullable',
            'body' => 'nullable',
            'feature' => 'nullable',
            'sku' => 'nullable',
            'purchase_prices' => 'nullable',
            'sell_price' => 'required|numeric',
            'regular_price' => 'nullable',
            'is_stock' => 'nullable',
            'video_link' => 'nullable',
            'is_video_active' => 'nullable',
            'discount_type' => 'nullable',
            'dicount_amount' => 'nullable',
            'after_discount' => 'nullable',
            'weight' => 'nullable|numeric', 
        ]);

        $data['user_id'] = auth()->user()->id;
        $data['is_stock'] = (int) ($request->is_stock ?? 0);
        $data['is_video_active'] = (int) ($request->is_video_active ?? 1);
        
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        $data['slug'] = $slug;

        $mainQty = (int) ($request->pro_quantity ?? 0);
        $data['stock_quantity'] = $mainQty > 0 ? $mainQty : 1; 

        DB::beginTransaction();
        try {

            if ($request->hasFile('image')) {
                $image = Image::make($request->file('image'));
                $extention = $request->image->getClientOriginalExtension();
                $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
                $destinationPath = public_path('products/');
                $image->resize(800, 800);
                $image->save($destinationPath . $image_name);
                $data['image'] = $image_name;

                $destinationPathThumbnail = public_path('thumb_products/');
                $image->resize(200, 200);
                $image->save($destinationPathThumbnail . $image_name);
            }

            $product = Product::create($data);

            if (isset($request->images)) {
                $image_data = [];
                foreach ($request->images as $image) {
                    $extention = $image->getClientOriginalExtension();
                    $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
                    $img = Image::make($image);
                    $destinationPath = public_path('products/');
                    $img->resize(800, 800);
                    $img->save($destinationPath . $image_name);
                    $image_data[] = ['image' => $image_name];
                }
                if (!empty($image_data)) {
                    $product->images()->createMany($image_data);
                }
            }

            if ($request->type == 'variable') {

                $sizes = (array) $request->size_id;
                $colors = (array) $request->color_id;
                $purchases = (array) $request->purchase_price;
                $prices = (array) $request->price;
                $after = (array) $request->after_discount_price;
                $qtys = (array) $request->quantity;
                $varImages = $request->file('variation_image'); 

                $count = max(count($sizes), count($colors), count($prices));

                for ($i = 0; $i < $count; $i++) {
                    if (empty($sizes[$i]) && empty($colors[$i]) && empty($prices[$i])) continue;

                    $vQty = (int) ($qtys[$i] ?? 0);
                    $qty = $vQty > 0 ? $vQty : 1; 

                    $varImageName = null;
                    if (isset($varImages[$i]) && $varImages[$i]->isValid()) {
                        $vImage = $varImages[$i];
                        $ext = $vImage->getClientOriginalExtension();
                        $varImageName = 'var-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
                        $vImage->move(public_path('products/'), $varImageName);
                    }

                    $var = Variation::create([
                        'product_id' => $product->id,
                        'size_id' => !empty($sizes[$i]) ? $sizes[$i] : null,
                        'color_id' => !empty($colors[$i]) ? $colors[$i] : null,
                        'image' => $varImageName, 
                        'purchase_price' => $purchases[$i] ?? 0,
                        'price' => $prices[$i] ?? 0,
                        'after_discount_price' => $after[$i] ?? null,
                        'stock_quantity' => $qty,
                    ]);

                    ProductStock::create([
                        'product_id' => $product->id,
                        'variation_id' => $var->id,
                        'quantity' => $qty,
                    ]);
                }
            } else {
                $singleQty = (int) ($request->pro_quantity ?? 0);
                $qty = $singleQty > 0 ? $singleQty : 1;

                $var = Variation::create([
                    'product_id' => $product->id,
                    'size_id' => "3",
                    'color_id' => "1",
                    'purchase_price' => $request->purchase_prices,
                    'price' => $request->sell_price,
                    'after_discount_price' => $request->after_discount,
                    'stock_quantity' => $qty,
                ]);

                ProductStock::create([
                    'product_id' => $product->id,
                    'variation_id' => $var->id,
                    'quantity' => $qty,
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Product Is Created !!', 'url' => route('admin.products.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $product = Product::with('sizes', 'sizes.stocks')->find($id);
        return view('backend.products.show', compact('product'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        $item = Product::with(['sizes', 'images', 'variations'])->findOrFail($id);
        $cats = Category::whereNull('parent_id')->get();
        $sizes = Size::all();
        $types = Type::all();
        $colors = Color::all();
        $subs = Category::where('parent_id', $item->category_id)->get();

        return view('backend.products.edit', compact('item', 'cats', 'sizes', 'types', 'subs', 'colors'));
    }

    public function productCopy($id)
    {
        $item = Product::with('sizes')->find($id);
        $cats = Category::whereNull('parent_id')->get();
        $sizes = Size::all();
        $types = Type::all();
        $colors = Color::all();
        $subs = Category::where('parent_id', $item->category_id)->get();
        return view('backend.products.copy', compact('item', 'cats', 'sizes', 'types', 'subs', 'colors'));
    }

    public function update(Request $request, $id)
    {
        ini_set('memory_limit', '512M'); 

        if (!auth()->user()->can('product.edit')) {
            abort(403, 'unauthorized');
        }

        $product = Product::with(['variations', 'variations.stocks'])->findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'type' => 'required|in:single,variable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', 
            'category_id' => 'required',
            'sub_category_id' => 'nullable',
            'type_id' => 'nullable',
            'short_description' => 'nullable',
            'description' => 'nullable',
            'body' => 'nullable',
            'feature' => 'nullable',
            'sku' => 'nullable',
            'purchase_prices' => 'nullable',
            'regular_price' => 'nullable',
            'sell_price' => 'required|numeric',
            'is_stock' => 'nullable',
            'video_link' => 'nullable',
            'is_video_active' => 'nullable',
            'discount_type' => 'nullable',
            'dicount_amount' => 'nullable',
            'after_discount' => 'nullable',
            'weight' => 'nullable|numeric', 
        ]);

        $data['after_discount'] = $request->after_discount;
        $data['dicount_amount'] = $request->dicount_amount;
        $data['is_stock'] = (int) ($request->is_stock ?? 0);
        $data['is_video_active'] = (int) ($request->is_video_active ?? 1);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        $data['slug'] = $slug;

        $mainQty = (int) ($request->pro_quantity ?? 0);
        $data['stock_quantity'] = $mainQty > 0 ? $mainQty : 1; 

        DB::beginTransaction();
        try {

            if ($request->hasFile('image')) {
                deleteImage('products', $product->image);

                $image = Image::make($request->file('image'));
                $extention = $request->image->getClientOriginalExtension();
                $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;

                $destinationPath = public_path('products/');
                $image->resize(800, 800);
                $image->save($destinationPath . $image_name);
                $data['image'] = $image_name;

                deleteImage('thumb_products', $product->image);
                $destinationPathThumbnail = public_path('thumb_products/');
                $image->resize(200, 200);
                $image->save($destinationPathThumbnail . $image_name);
            }

            if (isset($request->images)) {
                $image_data = [];
                foreach ($request->images as $image) {
                    $extention = $image->getClientOriginalExtension();
                    $image_name = Str::slug($request->name) . date('-Y-m-d-h-i-s-') . rand(999, 9999) . '.' . $extention;
                    $img = Image::make($image);
                    $destinationPath = public_path('products/');
                    $img->resize(800, 800);
                    $img->save($destinationPath . $image_name);
                    $image_data[] = ['image' => $image_name];
                }
                if (!empty($image_data)) {
                    $product->images()->createMany($image_data);
                }
            }

            $product->update($data);

            if ($request->type === 'single') {

                $oldVars = $product->variations;
                foreach($oldVars as $oldVar) {
                    if ($oldVar->image) {
                        deleteImage('products', $oldVar->image);
                    }
                }

                $oldVarIds = $product->variations->pluck('id')->toArray();
                if (!empty($oldVarIds)) {
                    ProductStock::where('product_id', $product->id)->whereIn('variation_id', $oldVarIds)->delete();
                }
                Variation::where('product_id', $product->id)->delete();

                $singleQty = (int) ($request->pro_quantity ?? 0);
                $qty = $singleQty > 0 ? $singleQty : 1;

                $singleVar = Variation::create([
                    'product_id' => $product->id,
                    'size_id' => "3",
                    'color_id' => "1",
                    'purchase_price' => $request->purchase_prices ?? 0,
                    'price' => $request->sell_price ?? 0,
                    'after_discount_price' => $request->after_discount,
                    'stock_quantity' => $qty,
                ]);

                ProductStock::updateOrCreate(
                    ['product_id' => $product->id, 'variation_id' => $singleVar->id],
                    ['quantity' => $qty]
                );

                DB::commit();
                return response()->json(['status' => true, 'msg' => 'Product Is Updated !!', 'url' => route('admin.products.index')]);
            }

            $variationIdsReq = (array) $request->variation_id;
            $sizeIds = (array) $request->size_id;
            $colorIds = (array) $request->color_id;
            $purchasePrices = (array) $request->purchase_price;
            $prices = (array) $request->price;
            $afterPrices = (array) $request->after_discount_price;
            $qtys = (array) $request->quantity;
            $varImages = $request->file('variation_image'); 

            if (!empty($variationIdsReq)) {
                $delete_variations = Variation::where('product_id', $product->id)
                    ->whereNotIn('id', array_filter($variationIdsReq))
                    ->get();

                foreach ($delete_variations as $dv) {
                    if ($dv->image) {
                        deleteImage('products', $dv->image);
                    }
                    ProductStock::where('product_id', $product->id)->where('variation_id', $dv->id)->delete();
                    $dv->delete();
                }
            }

            $count = max(count($sizeIds), count($colorIds), count($prices));

            for ($i = 0; $i < $count; $i++) {
                if (empty($sizeIds[$i]) && empty($colorIds[$i]) && empty($prices[$i])) continue;

                $vId = $variationIdsReq[$i] ?? null;
                
                $vQty = (int) ($qtys[$i] ?? 0);
                $qty = $vQty > 0 ? $vQty : 1;
                
                $currentSize = !empty($sizeIds[$i]) ? $sizeIds[$i] : null;
                $currentColor = !empty($colorIds[$i]) ? $colorIds[$i] : null;

                $varImageName = null;
                if (isset($varImages[$i]) && $varImages[$i]->isValid()) {
                    $vImage = $varImages[$i];
                    $ext = $vImage->getClientOriginalExtension();
                    $varImageName = 'var-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
                    $vImage->move(public_path('products/'), $varImageName);
                }

                if ($vId) {
                    $variable = Variation::where('id', $vId)->where('product_id', $product->id)->first();
                    if (!$variable) continue;

                    if ($varImageName && $variable->image) {
                        deleteImage('products', $variable->image);
                    }

                    $variable->size_id = $currentSize;
                    $variable->color_id = $currentColor;
                    if ($varImageName) {
                        $variable->image = $varImageName; 
                    }
                    $variable->purchase_price = $purchasePrices[$i] ?? 0;
                    $variable->price = $prices[$i] ?? 0;
                    $variable->after_discount_price = $afterPrices[$i] ?? null;
                    $variable->stock_quantity = $qty;
                    $variable->save();

                    ProductStock::updateOrCreate(
                        ['product_id' => $product->id, 'variation_id' => $variable->id],
                        ['quantity' => $qty]
                    );
                } else {
                    $newVar = Variation::create([
                        'product_id' => $product->id,
                        'size_id' => $currentSize,
                        'color_id' => $currentColor,
                        'image' => $varImageName, 
                        'purchase_price' => $purchasePrices[$i] ?? 0,
                        'price' => $prices[$i] ?? 0,
                        'after_discount_price' => $afterPrices[$i] ?? null,
                        'stock_quantity' => $qty,
                    ]);

                    ProductStock::create([
                        'product_id' => $product->id,
                        'variation_id' => $newVar->id,
                        'quantity' => $qty,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Product Is Updated !!', 'url' => route('admin.products.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function updatePriority(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->priority = $request->input('priority');
            $product->save();
            return response()->json(['message' => 'Priority updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cat_wise_product(Request $request)
    {
        $cat_id = $request->category_id;
        $items = Product::with('category')->where('category_id', $request->category_id)->orderBy('id', 'desc')->paginate(30);
        $categories = Category::where('parent_id', null)->get();
        return view('backend.products.index', compact('items', 'categories', 'cat_id'));
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'unauthorized');
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            deleteImage('products', $product->image);
            deleteImage('products', $product->optional_image);

            if ($product->images()->count()) {
                foreach ($product->images as $image) {
                    deleteImage('products', $image->image);
                }
                $product->images()->delete();
            }

            foreach ($product->variations as $dv) {
                if ($dv->image) {
                    deleteImage('products', $dv->image);
                }
                ProductStock::where('product_id', $product->id)->where('variation_id', $dv->id)->delete();
                $dv->delete();
            }

            $product->delete();
            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Product Is Deleted !!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function deleteImage($id)
    {
        $item = ProductImage::findOrFail($id);
        deleteImage('products', $item->image);
        $item->delete();
        return back();
    }

    public function fileUpload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('ck-images'), $fileName);
            $url = asset('ck-images/' . $fileName);

            if ($request->has('CKEditorFuncNum')) {
                $CKEditorFuncNum = $request->input('CKEditorFuncNum');
                $msg = 'Image uploaded successfully';
                $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
                @header('Content-type: text/html; charset=utf-8');
                echo $response;
            } else {
                return response()->json(['url' => $url]);
            }
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    public function recommendedUpdate()
    {
        $status = (request('is_recommended') == 1) ? 1 : null;
        DB::table('products')->whereIn('id', request('product_ids'))->update(['is_recommended' => $status]);
        return response()->json(['status' => true, 'msg' => 'Product Status Updated !!']);
    }

    public function showUpdate()
    {
        $status = (request('status') == 1) ? 1 : 0;
        DB::table('products')->whereIn('id', request('product_ids'))->update(['status' => $status]);
        return response()->json(['status' => true, 'msg' => 'Product Status Updated !!']);
    }

    public function forYouUpdate(Request $request)
    {
        if (empty($request->product_ids)) {
            return response()->json(['status' => false, 'msg' => 'Please select at least one product.']);
        }
        $status = $request->is_for_you ?? 0;
        Product::whereIn('id', $request->product_ids)->update(['is_for_you' => $status]);
        $msg = $status == 1 ? 'Added to For You successfully!' : 'Removed from For You successfully!';
        return response()->json(['status' => true, 'msg' => $msg]);
    }

    public function stockWarningIndex()
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'unauthorized');
        }

        $information = \App\Models\Information::orderBy('id', 'desc')->first();
        $threshold = $information->stock_warning_limit ?? 5; 

        $items = ProductStock::with(['product', 'variation.size', 'variation.color'])
                    ->where('quantity', '<=', $threshold)
                    ->orderBy('quantity', 'asc')
                    ->paginate(30);

        return view('backend.products.stock_warning', compact('items', 'threshold'));
   }
}