<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Product::with('thumbs:name')->latest()->get());
    }

    public function trending(Request $request): JsonResponse
    {
        return response()->json(Product::with('thumbs:name')->where('status', 'active')->latest()->limit(8)->get());
    }

    public function show(String $name): JsonResponse
    {
        return response()->json(Product::with(['thumbs:name', 'user:id,name,rating,rating'])->where('name', $name)->first());
    }

    public function showbyid(Product $product): JsonResponse
    {
        return response()->json($product->load(['thumbs:name', 'user:id,name,rating', 'order']));
    }

    public function related(String $name): JsonResponse
    {
        $subcategory = Product::where('name', $name)->first()->subcategory_id;
        return response()->json(Product::with('thumbs:name')->where('subcategory_id', $subcategory)->inRandomOrder()->limit(4)->get());
    }

    public function sell(Request $request): JsonResponse
    {
        try {
            $user = request()->user();
            // logger()->info($request->all());

            $product = Product::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'rootcategory_id' => $request->rootcategory,
                'subcategory_id' => $request->subcategory,
                'status' => 'active',
            ]);

            // logger()->info($product);

            $fileName = Str::slug($request->name, '-') . '-' . time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(storage_path('app/public/product/'), $fileName);

            // logger()->info($fileName);

            $product->thumbs()->create([
                'name' => $fileName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
