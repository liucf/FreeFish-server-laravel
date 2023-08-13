<?php

namespace App\Http\Controllers;

use App\Models\Rootcategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Rootcategory::with('subCategory:rootcategory_id,name,id')->get();
        return response()->json($categories);
    }
}
