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
        ]);

        $result = $aiService->simplifyTask(
            $request->input('title'),
            $request->input('description')
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
        ]);

        $result = $aiService->generateTaskForMentor($request->input('topic'));

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
