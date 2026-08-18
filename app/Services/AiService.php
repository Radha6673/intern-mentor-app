<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Rewrite raw input text to be polite, clear, and professional.
     */
    public function polishText(string $rawText, string $context = 'chat'): string
    {
        $text = trim($rawText);
        if (empty($text)) {
            return '';
        }

        if ($this->apiKey) {
            try {
                $prompt = "You are an AI assistant in an Intern-Mentor workplace app. ";
                if ($context === 'submission') {
                    $prompt .= "Rewrite the following intern task submission explanation to be clear, professional, well-structured, and polite. Maintain the original core technical points.\n\nInput: \"{$text}\"";
                } else {
                    $prompt .= "Rewrite the following message to be polite, clear, and professional for a workplace chat between an intern and a mentor. Keep the message length reasonable.\n\nInput: \"{$text}\"";
                }

                $response = Http::timeout(12)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json('candidates.0.content.parts.0.text');
                    if ($result) {
                        return trim(str_replace(['"', '```'], '', $result));
                    }
                } else {
                    Log::warning('Gemini API polishText status: ' . $response->status() . ' body: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini API call failed for polishText: ' . $e->getMessage());
            }
        }

        // Smart Fallback if API Key is not configured or request fails
        return $this->fallbackPolish($text, $context);
    }

    /**
     * Simplify task requirements and provide step-by-step guidance for interns.
     */
    public function simplifyTask(string $title, string $description): array
    {
        if ($this->apiKey) {
            try {
                $prompt = "You are an expert AI tech mentor helping an intern complete their specific task.\n"
                    . "Task Title: {$title}\n"
                    . "Task Description: {$description}\n\n"
                    . "Respond ONLY in valid JSON format with three keys:\n"
                    . "\"summary\": A short, simple 2-3 sentence summary explaining what this specific task requires.\n"
                    . "\"steps\": An array of 4-6 actionable, highly specific step-by-step instructions specifically tailored to complete '{$title}'.\n"
                    . "\"tips\": An array of 2-3 key technical tips, best practices, or tools specifically relevant to '{$title}'.\n"
                    . "Do not include markdown code block backticks around JSON.";

                $response = Http::timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text');
                    $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
                    $data = json_decode($cleanJson, true);
                    if ($data && isset($data['summary'], $data['steps'])) {
                        return [
                            'summary' => $data['summary'],
                            'steps' => (array) $data['steps'],
                            'tips' => isset($data['tips']) ? (array) $data['tips'] : ['Test your implementation against edge cases.', 'Double-check requirements before submitting.']
                        ];
                    }
                } else {
                    Log::warning('Gemini API simplifyTask status: ' . $response->status() . ' body: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini API call failed for simplifyTask: ' . $e->getMessage());
            }
        }

        // Fallback Task Simplifier
        return $this->fallbackSimplify($title, $description);
    }

    /**
     * Auto-generate Task Title and Description for Mentors based on a brief topic.
     */
    public function generateTaskForMentor(string $topic): array
    {
        $topic = trim($topic);

        if ($this->apiKey) {
            try {
                $prompt = "You are an expert tech mentor creating an internship task assignment.\n"
                    . "Topic/Idea: {$topic}\n\n"
                    . "Respond ONLY in valid JSON format with two keys:\n"
                    . "\"title\": A concise, clear task title.\n"
                    . "\"description\": A detailed description specifying objectives, requirements, step-by-step instructions, and deliverables.\n"
                    . "Do not include markdown code block backticks around JSON.";

                $response = Http::timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text');
                    $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
                    $data = json_decode($cleanJson, true);
                    if ($data && isset($data['title'], $data['description'])) {
                        return [
                            'title' => $data['title'],
                            'description' => $data['description']
                        ];
                    }
                } else {
                    Log::warning('Gemini API generateTaskForMentor status: ' . $response->status() . ' body: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini API call failed for generateTaskForMentor: ' . $e->getMessage());
            }
        }

        // Fallback Task Generator
        return $this->fallbackGenerateTask($topic);
    }

    /**
     * Fallback Polish generator
     */
    protected function fallbackPolish(string $text, string $context): string
    {
        $text = ucfirst(trim($text));
        if (!preg_match('/[.!?]$/', $text)) {
            $text .= '.';
        }

        if ($context === 'submission') {
            return "Respected Mentor, here is the detailed breakdown of my solution:\n\n"
                . $text . "\n\n"
                . "I have verified the functionality and ensured code quality. Please review at your convenience.";
        }

        return "Hello, " . lcfirst($text) . " Thank you for your time and guidance.";
    }

    /**
     * Dynamic Task-Specific Fallback Simplifier
     */
    protected function fallbackSimplify(string $title, string $description): array
    {
        $text = strtolower($title . ' ' . $description);

        $summary = "Task Goal: You need to implement '{$title}'. ";
        $steps = [];
        $tips = [];

        if (str_contains($text, 'auth') || str_contains($text, 'login') || str_contains($text, 'register') || str_contains($text, 'jwt')) {
            $summary .= "This task focuses on building secure user authentication and session logic.";
            $steps = [
                "1. Define required authentication endpoints/routes (login, register, logout).",
                "2. Create or update User models, database migrations, and password hashing.",
                "3. Implement request validation for user credentials (email, password format).",
                "4. Generate secure tokens/session cookies and handle invalid login error states.",
                "5. Test authentication routes and verify protected endpoint access."
            ];
            $tips = [
                "Always hash user passwords securely using Hash::make().",
                "Ensure protected routes apply auth middleware."
            ];
        } elseif (str_contains($text, 'api') || str_contains($text, 'rest') || str_contains($text, 'crud') || str_contains($text, 'endpoint')) {
            $summary .= "This task requires creating structured REST API endpoints and data responses.";
            $steps = [
                "1. Define API routes in routes/api.php or routes/web.php with HTTP methods (GET, POST, PUT, DELETE).",
                "2. Create dedicated Controller methods for handling CRUD logic.",
                "3. Implement request validation for incoming payload parameters.",
                "4. Format responses cleanly with proper HTTP status codes (200 OK, 201 Created, 422 Error).",
                "5. Test API endpoints using Postman or cURL."
            ];
            $tips = [
                "Use Laravel Eloquent API Resources for consistent JSON structures.",
                "Validate payload data before processing database queries."
            ];
        } elseif (str_contains($text, 'ui') || str_contains($text, 'vue') || str_contains($text, 'frontend') || str_contains($text, 'page') || str_contains($text, 'tailwind')) {
            $summary .= "This task involves developing dynamic frontend UI components and user interactions.";
            $steps = [
                "1. Break down the layout requirements into modular Vue components.",
                "2. Set up reactive component state (ref/reactive) for form fields and UI states.",
                "3. Style elements using responsive Tailwind CSS utility classes.",
                "4. Connect UI events and submit handlers to backend endpoints via Axios/Inertia.",
                "5. Verify UI responsiveness on both mobile and desktop screens."
            ];
            $tips = [
                "Handle loading and disabled button states during API requests.",
                "Check browser developer tools console for reactive prop warnings."
            ];
        } elseif (str_contains($text, 'upload') || str_contains($text, 'image') || str_contains($text, 'file') || str_contains($text, 'media') || str_contains($text, 'video')) {
            $summary .= "This task focuses on handling media/file uploads and file storage.";
            $steps = [
                "1. Add file input elements with format and size constraints in the frontend form.",
                "2. Implement server-side validation for file extensions and mime-types.",
                "3. Save uploaded files to storage/app/public using Laravel Storage facade.",
                "4. Save relative file path references into the database record.",
                "5. Verify image/media previews and download links in the UI."
            ];
            $tips = [
                "Ensure 'php artisan storage:link' is executed.",
                "Always validate file extensions on the backend for security."
            ];
        } else {
            $summary .= "Focus on reviewing the task requirements and building the required functionality step-by-step.";

            // Extract lines/bullets directly from description if provided
            $lines = array_values(array_filter(array_map('trim', explode("\n", $description))));
            $extractedSteps = [];
            foreach ($lines as $line) {
                if (strlen($line) > 10 && !str_starts_with($line, '#')) {
                    $extractedSteps[] = "Implement: " . rtrim($line, '.');
                }
            }

            if (count($extractedSteps) >= 3) {
                $steps = array_slice($extractedSteps, 0, 5);
            } else {
                $steps = [
                    "1. Analyze the core requirements for '{$title}'.",
                    "2. Set up your local feature branch and necessary file structures.",
                    "3. Implement core backend logic (Controller/Model/Database).",
                    "4. Connect frontend components and test edge-case inputs.",
                    "5. Review code quality and submit your solution with clear notes."
                ];
            }

            $tips = [
                "Maintain clean code standards and comments.",
                "Reach out to your mentor if you face blocking issues."
            ];
        }

        return [
            'summary' => $summary,
            'steps' => $steps,
            'tips' => $tips
        ];
    }

    /**
     * Fallback Mentor Task Generator
     */
    protected function fallbackGenerateTask(string $topic): array
    {
        $formattedTopic = ucwords(trim($topic));
        return [
            'title' => "Implement " . ($formattedTopic ?: "New Feature Assignment"),
            'description' => "Objective:\nComplete the implementation for: " . ($topic ?: "assigned module") . ".\n\n"
                . "Requirements:\n"
                . "1. Analyze module requirements and design a clean technical approach.\n"
                . "2. Implement necessary logic, database updates, and UI views.\n"
                . "3. Test all edge cases and ensure responsive design and code standards.\n\n"
                . "Deliverables:\n"
                . "- Code changes pushed to repository\n"
                . "- Brief explanation of your technical implementation upon submission."
        ];
    }
}
