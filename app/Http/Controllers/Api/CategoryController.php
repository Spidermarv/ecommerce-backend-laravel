<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Optionally, eager load products count or products themselves
        // For just names: return response()->json(Category::all());
        // For names with product count: return response()->json(Category::withCount('products')->get());
        // For names with all product details (can be large): return response()->json(Category::with('products')->get());

        return response()->json(Category::withCount('products')->paginate(10));
    }

    // You can add show, store, update, destroy methods here if needed later

}