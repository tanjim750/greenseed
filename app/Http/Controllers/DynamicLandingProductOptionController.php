<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynamicLandingProductOptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->select(['id', 'name', 'sku', 'sell_price', 'regular_price', 'after_discount', 'status'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($where) use ($search) {
                    $where->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->where(fn ($query) => $query->whereNull('status')->orWhereIn('status', [1, '1', true, 'active', 'Active']))
            ->latest('id')
            ->limit($search === '' ? 500 : 50)
            ->get()
            ->map(fn (Product $product) => [
                'id' => (int) $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) ($product->after_discount ?: $product->sell_price ?: $product->regular_price ?: 0),
                'label' => trim($product->name . ($product->sku ? " ({$product->sku})" : '')),
            ])
            ->values();

        return response()->json([
            'data' => $products,
        ]);
    }
}
