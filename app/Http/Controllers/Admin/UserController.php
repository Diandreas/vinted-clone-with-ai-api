<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index() { return response()->json(['success' => true, 'data' => []]); }
    public function show($user) { return response()->json(['success' => true, 'data' => []]); }
    public function verify($user) { return response()->json(['success' => true]); }
    public function ban($user) { return response()->json(['success' => true]); }
    public function unban($user) { return response()->json(['success' => true]); }
    public function destroy($user) { return response()->json(['success' => true]); }
}