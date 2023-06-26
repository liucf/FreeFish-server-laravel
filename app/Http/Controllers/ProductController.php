<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json( Product::with('thumbs:name')->get() );
    }
}
