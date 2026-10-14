<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        // Get all active products with their weight variants
        $products = Product::where('is_active', true)->with('variants')->get();
        
        return view('store.home', compact('products'));
    }
}