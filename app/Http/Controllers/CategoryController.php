<?php

namespace App\Http\Controllers;

use App\Models\Rootcategory;
use Illuminate\Http\Request;

/**
 * Class CategoryController
 *
 * This class is responsible for handling requests related to categories.
 * It extends the base Controller class.
 */
class CategoryController extends Controller
{

    /**
     * Retrieve all root categories with their corresponding subcategories.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categories = Rootcategory::with('subCategory:rootcategory_id,name,id')->get();
        return response()->json($categories);
    }
}
