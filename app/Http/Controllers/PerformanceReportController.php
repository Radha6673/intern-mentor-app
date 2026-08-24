<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PerformanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReportController extends Controller
{
    public function __construct(
        protected PerformanceReportService $reportService
    ) {}

    /**
     * Get performance report for a specific intern or overall team summary.
     */
    public function show(Request $request, ?User $user = null): JsonResponse
    {
        $currentUser = Auth::user();

        if (!in_array($currentUser->role, [UserRole::ADMIN->value, UserRole::MENTOR->value])) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        if ($user && $user->id) {
            if ($user->role !== UserRole::INTERN->value) {
                return response()->json(['error' => 'User is not an intern.'], 422);
            }

            $report = $this->reportService->generateSingleInternReport($user);
            return response()->json($report);
        }

        $teamReport = $this->reportService->generateTeamReport();
        return response()->json($teamReport);
    }
}
