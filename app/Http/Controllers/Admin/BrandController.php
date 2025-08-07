<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function store(Request $request) { return response()->json(['success' => true, 'data' => []], 201); }
    public function update($brand, Request $request) { return response()->json(['success' => true, 'data' => []]); }
    public function destroy($brand) { return response()->json(['success' => true]); }
}