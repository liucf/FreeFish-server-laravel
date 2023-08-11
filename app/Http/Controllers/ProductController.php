<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json( Product::with('thumbs:name')->latest()->get() );
    }

    public function trending(Request $request): JsonResponse
    {
        return response()->json( Product::with('thumbs:name')->where('status', 'active')->limit(8)->get() );
    }

    public function show (String $name) : JsonResponse
    {
        return response()->json( Product::with(['thumbs:name', 'user:id,name,rating,rating'])->where('name' , $name)->first() );
    }

    public function showbyid (Product $product) : JsonResponse
    {
        return response()->json($product->load(['thumbs:name', 'user:id,name,rating', 'order']) );
    } 

    public function related(String $name): JsonResponse
    {
        $subcategory = Product::where('name' , $name)->first()->subcategory_id;
        return response()->json( Product::with('thumbs:name')->where('subcategory_id' , $subcategory)->inRandomOrder()->limit(4)->get() );
    }
}
