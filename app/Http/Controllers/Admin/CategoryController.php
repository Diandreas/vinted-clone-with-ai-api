<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request) { return response()->json(['success' => true, 'data' => []], 201); }
    public function update($category, Request $request) { return response()->json(['success' => true, 'data' => []]); }
    public function destroy($category) { return response()->json(['success' => true]); }
}