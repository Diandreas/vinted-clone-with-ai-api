<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    public function overview() { return response()->json(['success' => true, 'data' => []]); }
    public function users() { return response()->json(['success' => true, 'data' => []]); }
    public function products() { return response()->json(['success' => true, 'data' => []]); }
    public function sales() { return response()->json(['success' => true, 'data' => []]); }
    public function reports() { return response()->json(['success' => true, 'data' => []]); }
}