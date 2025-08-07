<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index() { return response()->json(['success' => true, 'data' => []]); }
    public function update(Request $request) { return response()->json(['success' => true, 'data' => []]); }
}