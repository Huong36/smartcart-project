<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', ['products' => Product::with('category')->latest('id')->take(8)->get()]);
    }
}
