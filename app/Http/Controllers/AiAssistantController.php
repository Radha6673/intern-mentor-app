<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiService;

class AiAssistantController extends Controller
{
    /**
     * Polish input text to make it polite and professional.
     */
    public function polishText(Request $request, AiService $aiService)
    {
        $request->validate([
            'text' => 'required|string|max:2000',
            'context' => 'nullable|string|in:chat,submission',
        ]);

        $context = $request->input('context', 'chat');
        $polished = $aiService->polishText($request->input('text'), $context);

        return response()->json([
            'success' => true,
            'polished_text' => $polished,
        ]);
    }

    /**
     * Simplify task requirements & provide step-by-step guidance.
     */
    public function simplifyTask(Request $request, AiService $aiService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'nullable|string|max:100',
        ]);

        $department = $request->input('department') ?? $request->user()?->department;

        $result = $aiService->simplifyTask(
            $request->input('title'),
            $request->input('description'),
            $department
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Auto-generate Task Title & Description for Mentors.
     */
    public function generateTask(Request $request, AiService $aiService)
    {
        $request->validate([
            'topic' => 'required|string|max:500',
            'department' => 'nullable|string|max:100',
            'intern_id' => 'nullable|exists:users,id',
        ]);

        $department = $request->input('department');

        // If intern_id is provided and department was not explicitly passed, infer from intern
        if (!$department && $request->filled('intern_id')) {
            $intern = \App\Models\User::find($request->input('intern_id'));
            $department = $intern?->department;
        }

        $result = $aiService->generateTaskForMentor($request->input('topic'), $department);

        return response()->json([
            'success' => true,
            'data' => $result,
            'department' => $department,
        ]);
    }
}
