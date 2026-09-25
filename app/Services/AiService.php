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
                $prompt = "You are an AI assistant in SkillUp, an Intern-Mentor workplace platform. ";
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
    public function simplifyTask(string $title, string $description, ?string $department = null): array
    {
        $deptLabel = $department ? (\App\Enums\Department::tryFrom($department)?->label() ?? ucwords(str_replace('_', ' ', $department))) : null;

        if ($this->apiKey) {
            try {
                $deptPrompt = $deptLabel ? " The intern is working in the '{$deptLabel}' department. Provide guidance, steps, and technical tips specifically suited for a {$deptLabel}." : "";

                $prompt = "You are an expert AI tech mentor helping an intern complete their specific task.{$deptPrompt}\n"
                    . "Task Title: {$title}\n"
                    . "Task Description: {$description}\n"
                    . ($deptLabel ? "Intern Department: {$deptLabel}\n" : "")
                    . "\nRespond ONLY in valid JSON format with three keys:\n"
                    . "\"summary\": A short, simple 2-3 sentence summary explaining what this specific task requires.\n"
                    . "\"steps\": An array of 4-6 actionable, highly specific step-by-step instructions specifically tailored to complete '{$title}' for {$deptLabel}.\n"
                    . "\"tips\": An array of 2-3 key technical tips, best practices, or tools specifically relevant to '{$title}' and {$deptLabel}.\n"
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
        return $this->fallbackSimplify($title, $description, $department);
    }

    /**
     * Auto-generate Task Title and Description for Mentors based on a brief topic and intern department.
     */
    public function generateTaskForMentor(string $topic, ?string $department = null): array
    {
        $topic = trim($topic);
        $deptLabel = $department ? (\App\Enums\Department::tryFrom($department)?->label() ?? ucwords(str_replace('_', ' ', $department))) : null;

        if ($this->apiKey) {
            try {
                $deptPromptPart = $deptLabel ? " The assigned intern belongs to the '{$deptLabel}' department. Tailor all instructions, technical stacks, tools, frameworks, best practices, and expected deliverables specifically for a {$deptLabel} role." : "";

                $prompt = "You are an expert mentor creating a structured internship task assignment in SkillUp.{$deptPromptPart}\n"
                    . "Topic/Idea: {$topic}\n"
                    . ($deptLabel ? "Target Role / Department: {$deptLabel}\n" : "")
                    . "\nRespond ONLY in valid JSON format with two keys:\n"
                    . "\"title\": A concise, clear task title relevant to {$deptLabel}.\n"
                    . "\"description\": A detailed description specifying objectives, requirements, step-by-step instructions, and deliverables tailored for {$deptLabel}.\n"
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

        // Fallback Task Generator with department specialization
        return $this->fallbackGenerateTask($topic, $department);
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
    protected function fallbackSimplify(string $title, string $description, ?string $department = null): array
    {
        $text = strtolower($title . ' ' . $description);
        $deptLabel = $department ? (\App\Enums\Department::tryFrom($department)?->label() ?? ucwords(str_replace('_', ' ', $department))) : null;

        $summary = "Task Goal: You need to implement '{$title}'" . ($deptLabel ? " for the {$deptLabel} department. " : ". ");
        $steps = [];
        $tips = [];

        if ($department === 'android_developer') {
            $summary .= "This task requires native Android development in Kotlin.";
            $steps = [
                "1. Set up project dependencies and manifest permissions in Android Studio.",
                "2. Design the user interface using Jetpack Compose or XML layout.",
                "3. Implement ViewModel and Repository classes following MVVM pattern.",
                "4. Handle data persistence (Room DB) or networking (Retrofit/OkHttp).",
                "5. Test on Android Emulator or physical device and handle lifecycle edge cases."
            ];
            $tips = [
                "Ensure proper state management in Compose or LiveData/StateFlow.",
                "Always handle configuration changes (like screen rotation) cleanly."
            ];
        } elseif ($department === 'ios_developer') {
            $summary .= "This task requires native iOS development using Swift and Xcode.";
            $steps = [
                "1. Create SwiftUI views or UIKit ViewControllers for the required screens.",
                "2. Implement ViewModels and business logic with MVVM architecture.",
                "3. Integrate networking using URLSession or async/await API calls.",
                "4. Manage local state using @State, @Binding, or SwiftData/CoreData.",
                "5. Test responsiveness on different iPhone screen sizes and simulators."
            ];
            $tips = [
                "Adopt modern Swift Concurrency (async/await) for clean code.",
                "Follow Apple Human Interface Guidelines for accessibility."
            ];
        } elseif ($department === 'devops') {
            $summary .= "This task focuses on infrastructure, CI/CD automation, or containerization.";
            $steps = [
                "1. Analyze infrastructure requirements and target deployment environment.",
                "2. Write or update Dockerfile and Docker Compose configurations.",
                "3. Configure CI/CD automated test & build workflows (e.g. GitHub Actions).",
                "4. Secure sensitive credentials using environment variables and secrets manager.",
                "5. Verify container health checks, network ports, and logging."
            ];
            $tips = [
                "Keep Docker image sizes minimal using multi-stage builds.",
                "Never hardcode API keys or secret credentials in repository files."
            ];
        } elseif ($department === 'ai_developer') {
            $summary .= "This task focuses on artificial intelligence, model inference, or LLM engineering.";
            $steps = [
                "1. Set up Python environment and install required libraries (e.g. PyTorch, HuggingFace, Gemini/OpenAI SDK).",
                "2. Prepare input datasets, prompt templates, or feature embeddings.",
                "3. Implement model inference logic, preprocessing, and structured response parsing.",
                "4. Handle API latency, rate limits, and fallback error handling.",
                "5. Benchmark performance against sample test queries."
            ];
            $tips = [
                "Always validate model outputs against schema to prevent hallucinations.",
                "Implement robust caching for frequent model inference queries."
            ];
        } elseif ($department === 'business_analyst') {
            $summary .= "This task requires creating structured business documentation and requirements analysis.";
            $steps = [
                "1. Gather requirements and identify key stakeholders and target user personas.",
                "2. Draft comprehensive User Stories with standard Gherkin Acceptance Criteria (Given-When-Then).",
                "3. Design BPMN workflow diagrams or user journey flowcharts.",
                "4. Conduct gap analysis and document risks, dependencies, and business KPIs.",
                "5. Review requirements document with tech mentors and project stakeholders."
            ];
            $tips = [
                "Ensure acceptance criteria are testable, measurable, and unambiguous.",
                "Align functional specifications directly with company business goals."
            ];
        } elseif ($department === 'data_analyst') {
            $summary .= "This task requires querying, cleaning, and extracting actionable business insights from data.";
            $steps = [
                "1. Write SQL queries to extract relevant dataset tables from the database.",
                "2. Perform data cleaning, handling null values and anomalies using Python/Pandas.",
                "3. Conduct Exploratory Data Analysis (EDA) to discover patterns and trends.",
                "4. Build interactive charts or dashboards (Power BI / Tableau / Matplotlib).",
                "5. Summarize key business findings, insights, and recommended actions."
            ];
            $tips = [
                "Double-check SQL joins to prevent duplicate row inflations.",
                "Highlight actionable takeaways rather than just raw numbers."
            ];
        } elseif (str_contains($text, 'auth') || str_contains($text, 'login') || str_contains($text, 'register') || str_contains($text, 'jwt')) {
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
        } else {
            $summary .= "Focus on reviewing the task requirements and building the required functionality step-by-step.";
            $steps = [
                "1. Analyze the core requirements for '{$title}'.",
                "2. Set up your local feature branch and necessary file structures.",
                "3. Implement core backend and frontend logic.",
                "4. Verify responsive design and test edge-case inputs.",
                "5. Review code quality and submit your solution with clear notes."
            ];
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
     * Fallback Mentor Task Generator with Department Specialization
     */
    protected function fallbackGenerateTask(string $topic, ?string $department = null): array
    {
        $formattedTopic = ucwords(trim($topic)) ?: "Feature Assignment";

        return match ($department) {
            'android_developer' => [
                'title' => "Android: {$formattedTopic}",
                'description' => "Objective:\nDevelop native Android feature for: {$formattedTopic}.\n\n"
                    . "Technical Stack: Kotlin, Jetpack Compose / XML, Room DB, Retrofit REST API.\n\n"
                    . "Requirements:\n"
                    . "1. Design clean, responsive Android UI according to Material Design 3 guidelines.\n"
                    . "2. Implement MVVM architecture with ViewModel and StateFlow/LiveData.\n"
                    . "3. Connect API endpoints or local SQLite/Room database.\n"
                    . "4. Ensure graceful handling of offline state and orientation changes.\n\n"
                    . "Deliverables:\n"
                    . "- Push Kotlin code and Android Studio project to your Git branch.\n"
                    . "- Provide a screen recording or APK link along with brief architectural notes."
            ],
            'ios_developer' => [
                'title' => "iOS: {$formattedTopic}",
                'description' => "Objective:\nDevelop native iOS feature for: {$formattedTopic}.\n\n"
                    . "Technical Stack: Swift, SwiftUI, SwiftData / CoreData, URLSession.\n\n"
                    . "Requirements:\n"
                    . "1. Construct accessible, dynamic SwiftUI views adhering to Apple HIG.\n"
                    . "2. Structure business logic using MVVM architecture with @Observable or ObservableObject.\n"
                    . "3. Handle asynchronous data fetching using modern Swift async/await.\n"
                    . "4. Test UI on both light and dark mode across multiple device simulators.\n\n"
                    . "Deliverables:\n"
                    . "- Commit clean Swift code and Xcode project.\n"
                    . "- Submit implementation notes and screenshot previews."
            ],
            'devops' => [
                'title' => "DevOps: {$formattedTopic}",
                'description' => "Objective:\nAutomate infrastructure and deployment pipeline for: {$formattedTopic}.\n\n"
                    . "Technical Stack: Docker, Docker Compose, CI/CD (GitHub Actions / GitLab), Nginx, Linux.\n\n"
                    . "Requirements:\n"
                    . "1. Create optimized, secure multi-stage Dockerfile.\n"
                    . "2. Define service orchestration in docker-compose.yml with healthchecks and volumes.\n"
                    . "3. Implement automated lint, test, and build workflows in CI pipeline.\n"
                    . "4. Configure reverse proxy and manage secret environment variables securely.\n\n"
                    . "Deliverables:\n"
                    . "- Docker configurations, CI/CD YAML files, and setup documentation.\n"
                    . "- Verification logs demonstrating successful container build and automated pipeline run."
            ],
            'ai_developer' => [
                'title' => "AI/ML: {$formattedTopic}",
                'description' => "Objective:\nBuild intelligent AI/ML workflow or model integration for: {$formattedTopic}.\n\n"
                    . "Technical Stack: Python, Gemini / OpenAI API, PyTorch, HuggingFace, RAG.\n\n"
                    . "Requirements:\n"
                    . "1. Design structured prompt templates or load targeted pre-trained model.\n"
                    . "2. Implement data preprocessing, cleaning, and input validation.\n"
                    . "3. Build inference endpoint and parse model response strictly into required schema.\n"
                    . "4. Implement safety checks, error recovery, and caching for API cost optimization.\n\n"
                    . "Deliverables:\n"
                    . "- Python source code, notebook or API service, and requirements.txt.\n"
                    . "- Evaluation report comparing model output quality against sample test inputs."
            ],
            'business_analyst' => [
                'title' => "Business Analysis: {$formattedTopic}",
                'description' => "Objective:\nPrepare comprehensive Business Requirements Document (BRD) and analysis for: {$formattedTopic}.\n\n"
                    . "Deliverable Format: PRD / BRD Specification, User Stories, BPMN Diagram.\n\n"
                    . "Requirements:\n"
                    . "1. Define business problem statement, target audience personas, and success metrics (KPIs).\n"
                    . "2. Write detailed User Stories with Gherkin acceptance criteria (Given, When, Then).\n"
                    . "3. Map out business process workflows and system interaction flowcharts.\n"
                    . "4. Document system dependencies, risk assessment, and non-functional requirements.\n\n"
                    . "Deliverables:\n"
                    . "- Complete PRD / BRD document link (Google Docs / Notion / Markdown).\n"
                    . "- Flowchart diagrams and prioritized backlog breakdown."
            ],
            'data_analyst' => [
                'title' => "Data Analysis: {$formattedTopic}",
                'description' => "Objective:\nAnalyze dataset, identify business trends, and generate insights for: {$formattedTopic}.\n\n"
                    . "Technical Stack: SQL, Python / Pandas, Tableau / Power BI / Matplotlib.\n\n"
                    . "Requirements:\n"
                    . "1. Write optimized SQL queries to aggregate and extract required metrics.\n"
                    . "2. Clean and preprocess data, treating missing values and anomalies.\n"
                    . "3. Conduct Exploratory Data Analysis (EDA) to uncover trends and correlations.\n"
                    . "4. Build visual dashboard cards and charts illustrating key business indicators.\n\n"
                    . "Deliverables:\n"
                    . "- SQL script files or Jupyter Notebook with clean comments.\n"
                    . "- Executive summary report highlighting actionable recommendations based on data findings."
            ],
            default => [
                'title' => "Web Dev: {$formattedTopic}",
                'description' => "Objective:\nComplete full-stack implementation for: {$formattedTopic}.\n\n"
                    . "Technical Stack: Laravel, Vue 3, Inertia.js, Tailwind CSS, MySQL.\n\n"
                    . "Requirements:\n"
                    . "1. Create required database migrations, models, and Eloquent relationships.\n"
                    . "2. Implement secure Controller logic with FormRequest validations.\n"
                    . "3. Develop modern, responsive Vue 3 components with Tailwind CSS styling.\n"
                    . "4. Ensure end-to-end functionality, handling loading and error states.\n\n"
                    . "Deliverables:\n"
                    . "- Pull Request with clean commits pushed to repository.\n"
                    . "- Submission note summarizing code changes and testing steps."
            ],
        };
    }
}
