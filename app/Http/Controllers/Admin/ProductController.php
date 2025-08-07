<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index() { return response()->json(['success' => true, 'data' => []]); }
    public function pending() { return response()->json(['success' => true, 'data' => []]); }
    public function approve($product) { return response()->json(['success' => true]); }
    public function reject($product) { return response()->json(['success' => true]); }
    public function feature($product) { return response()->json(['success' => true]); }
    public function destroy($product) { return response()->json(['success' => true]); }
}