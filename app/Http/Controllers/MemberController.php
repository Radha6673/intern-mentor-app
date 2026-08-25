<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MemberController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Display member list based on the authenticated user's role.
     */
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        $user = $request->user();
        $members = $this->userRepository->getMembersForUser($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user_role' => $user->role,
                'data' => $members,
            ]);
        }

        return Inertia::render('Members/Index', [
            'members' => $members,
            'userRole' => $user->role,
        ]);
    }
}
