<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index() { return response()->json(['success' => true, 'data' => []]); }
    public function show($report) { return response()->json(['success' => true, 'data' => []]); }
    public function resolve($report) { return response()->json(['success' => true]); }
    public function dismiss($report) { return response()->json(['success' => true]); }
}