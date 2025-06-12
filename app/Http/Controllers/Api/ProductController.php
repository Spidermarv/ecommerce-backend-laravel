<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
{
    // Eager load category for better performance
    return response()->json(Product::with('category')->paginate(10));
}

    public function show($id)
{
    return response()->json(Product::with('category')->findOrFail($id));
}

}
