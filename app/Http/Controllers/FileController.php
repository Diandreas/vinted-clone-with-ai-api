<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function serve($path)
    {
        // Construct the full path
        $filePath = 'public/' . $path;
        
        // Log for debugging
        \Log::info('File request', [
            'requested_path' => $path,
            'full_path' => $filePath,
            'storage_exists' => Storage::exists($filePath)
        ]);
        
        // Check if file exists
        if (!Storage::exists($filePath)) {
            \Log::warning('File not found', ['path' => $filePath]);
            abort(404, 'File not found: ' . $path);
        }
        
        // Get file content and type
        $file = Storage::get($filePath);
        $mimeType = Storage::mimeType($filePath);
        
        // Return file response
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
