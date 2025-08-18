<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function serve($path)
    {
        // Use the public disk instead of default local disk
        $publicDisk = Storage::disk('public');
        
        // Log for debugging
        \Log::info('File request', [
            'requested_path' => $path,
            'public_disk_exists' => $publicDisk->exists($path)
        ]);
        
        // Check if file exists on public disk
        if (!$publicDisk->exists($path)) {
            \Log::warning('File not found', ['path' => $path]);
            abort(404, 'File not found: ' . $path);
        }
        
        // Get file content and type from public disk
        $file = $publicDisk->get($path);
        $mimeType = $publicDisk->mimeType($path);
        
        // Return file response
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
