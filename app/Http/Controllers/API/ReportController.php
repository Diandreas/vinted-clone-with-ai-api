<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Product;
use App\Models\User;
use App\Models\Live;
use App\Models\Story;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reportable_type' => 'required|in:product,user,live,story,message',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|in:spam,harassment,inappropriate,copyright,fake,violence,other',
            'description' => 'nullable|string|max:1000',
            'evidence_urls' => 'nullable|array',
            'evidence_urls.*' => 'url'
        ]);

        // Validate reportable exists
        $reportableClass = $this->getReportableClass($request->reportable_type);
        $reportable = $reportableClass::findOrFail($request->reportable_id);

        // Prevent self-reporting for user reports
        if ($request->reportable_type === 'user' && $reportable->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot report yourself'
            ], 400);
        }

        // Check if user already reported this item
        $existingReport = Report::where([
            'reported_by' => Auth::id(),
            'reportable_type' => $reportableClass,
            'reportable_id' => $request->reportable_id
        ])->first();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported this item'
            ], 400);
        }

        $report = Report::create([
            'reported_by' => Auth::id(),
            'reportable_type' => $reportableClass,
            'reportable_id' => $request->reportable_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'evidence_urls' => $request->evidence_urls,
            'status' => 'pending'
        ]);

        $report->load(['reportable', 'reporter']);

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'Report submitted successfully'
        ], 201);
    }

    public function myReports()
    {
        $reports = Report::where('reported_by', Auth::id())
            ->with(['reportable'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    private function getReportableClass($type)
    {
        $classes = [
            'product' => Product::class,
            'user' => User::class,
            'live' => Live::class,
            'story' => Story::class,
            'message' => Message::class,
        ];

        return $classes[$type] ?? null;
    }
}