<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class SubcategoryController extends Controller
{

    public function featured(Request $request): JsonResponse
    {
        return response()->json(Subcategory::with('rootcategory:id,name')->limit(6)->get());
    }

    public function show(String $name): JsonResponse
    {
        $sort = request()->query('sort');
        $order = 'desc';
        if ($sort == '' || $sort == 'newest') {
            $sort = 'created_at';
        } else if ($sort == 'priceasc') {
            $sort = 'price';
            $order = 'asc';
        } else if ($sort == 'pricedesc') {
            $sort = 'price';
            $order = 'desc';
        }

        return response()->json(
            Product::with('thumbs:name')
                ->where('status', 'active')
                ->where('subcategory_id', Subcategory::where('name', $name)->first()->id)
                ->orderBy($sort, $order)
                ->paginate(12)
        );
    }

    public function showbyid(int $id): JsonResponse
    {
        $sort = request()->query('sort');
        $order = 'desc';
        if ($sort == '' || $sort == 'newest') {
            $sort = 'created_at';
        } else if ($sort == 'priceasc') {
            $sort = 'price';
            $order = 'asc';
        } else if ($sort == 'pricedesc') {
            $sort = 'price';
            $order = 'desc';
        }

        return response()->json(
            Product::with('thumbs:name')
                ->where('status', 'active')
                ->where('subcategory_id', Subcategory::where('id', $id)->first()->id)
                ->orderBy($sort, $order)
                ->paginate(12)
        );
    }
}
