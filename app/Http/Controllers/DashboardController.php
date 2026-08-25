<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function __invoke(): Response
    {
        $user = Auth::user();
        $dashboardData = $this->dashboardService->getDashboardData($user);

        return Inertia::render('Dashboard', [
            'stats' => $dashboardData['stats'],
            'recentData' => $dashboardData['recentData'],
            'members' => $dashboardData['members'],
        ]);
    }
}
