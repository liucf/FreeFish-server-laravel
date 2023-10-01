<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;

/**
 * ReviewController class.
 *
 * This class extends the base Controller class and is responsible for handling review-related requests.
 *
 * @package App\Http\Controllers
 */
class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }

    /**
     * Retrieve reviews by user ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function byuser($id)
    {
        return response()->json(Review::with('author:id,name')->where('seller_id', $id)->latest()->get());
    }
}
