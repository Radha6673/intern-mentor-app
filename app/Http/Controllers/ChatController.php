<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Display chat page with conversations, contacts, and active message history.
     */
    public function index(Request $request, ?Conversation $conversation = null): Response
    {
        $authUser = Auth::user();
        $targetUserId = $request->query('user_id');

        if ($targetUserId && !$conversation) {
            $targetUser = User::find($targetUserId);
            if ($targetUser && $targetUser->id !== $authUser->id) {
                $conversation = $this->chatService->getOrCreateConversation($authUser, $targetUser);
            }
        }

        $conversations = $this->chatService->getUserConversations($authUser);
        $contacts = $this->chatService->getAvailableContacts($authUser);

        if (!$conversation && $conversations->isNotEmpty()) {
            $conversation = $conversations->first();
        }

        $activeMessages = [];
        $activeConversation = null;

        if ($conversation) {
            if ($conversation->mentor_id !== $authUser->id && $conversation->intern_id !== $authUser->id) {
                abort(403, 'Unauthorized access to this conversation.');
            }

            $this->chatService->markMessagesAsRead($conversation->id, $authUser->id);

            $conversation->load(['mentor:id,name,email', 'intern:id,name,email']);
            $conversation->other_user = $conversation->mentor_id === $authUser->id ? $conversation->intern : $conversation->mentor;
            $activeConversation = $conversation;

            $activeMessages = $this->chatService->getConversationMessages($conversation->id);
        }

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'contacts' => $contacts,
            'activeConversation' => $activeConversation,
            'messages' => $activeMessages,
        ]);
    }

    /**
     * Start or get a conversation with a specific contact user.
     */
    public function startConversation(User $user): RedirectResponse
    {
        $authUser = Auth::user();
        if ($user->id === $authUser->id) {
            return redirect()->route('chat.index');
        }

        $conversation = $this->chatService->getOrCreateConversation($authUser, $user);

        return redirect()->route('chat.index', ['conversation' => $conversation->id]);
    }

    /**
     * Send message with optional image, video, or file attachment.
     */
    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $authUser = Auth::user();

        if ($conversation->mentor_id !== $authUser->id && $conversation->intern_id !== $authUser->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:51200',
        ]);

        if (empty($request->message) && !$request->hasFile('file')) {
            return back()->withErrors(['message' => 'Please enter a message or select a file to send.']);
        }

        $this->chatService->sendMessage(
            $conversation,
            $authUser,
            $request->message,
            $request->file('file')
        );

        return back();
    }

    /**
     * Fetch latest messages for polling.
     */
    public function getMessages(Conversation $conversation): JsonResponse
    {
        $authUser = Auth::user();

        if ($conversation->mentor_id !== $authUser->id && $conversation->intern_id !== $authUser->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->chatService->markMessagesAsRead($conversation->id, $authUser->id);
        $messages = $this->chatService->getConversationMessages($conversation->id);

        return response()->json([
            'messages' => $messages,
        ]);
    }
}
