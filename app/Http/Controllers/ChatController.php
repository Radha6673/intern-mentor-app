<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Display chat page with conversations, contacts, and active message history.
     */
    public function index(Request $request, ?Conversation $conversation = null)
    {
        $authUser = Auth::user();

        // Target user specified in query param (e.g. /chat?user_id=3)
        $targetUserId = $request->query('user_id');

        if ($targetUserId && !$conversation) {
            $targetUser = User::find($targetUserId);
            if ($targetUser && $targetUser->id !== $authUser->id) {
                $conversation = $this->getOrCreateConversation($authUser, $targetUser);
            }
        }

        // Fetch user conversations
        $conversations = Conversation::with(['mentor:id,name,email', 'intern:id,name,email', 'latestMessage'])
            ->where('mentor_id', $authUser->id)
            ->orWhere('intern_id', $authUser->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($conv) use ($authUser) {
                $conv->unread_count = Message::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $authUser->id)
                    ->where('is_read', false)
                    ->count();
                $conv->other_user = $conv->mentor_id === $authUser->id ? $conv->intern : $conv->mentor;
                return $conv;
            });

        // Available contacts (Mentors for Interns, Interns for Mentors)
        $contacts = User::where('id', '!=', $authUser->id)
            ->when($authUser->role === 'mentor', function ($q) {
                return $q->where('role', 'intern');
            })
            ->when($authUser->role === 'intern', function ($q) {
                return $q->where('role', 'mentor');
            })
            ->select('id', 'name', 'email', 'role')
            ->get();

        // If no active conversation selected, pick the first available
        if (!$conversation && $conversations->isNotEmpty()) {
            $conversation = $conversations->first();
        }

        $activeMessages = [];
        $activeConversation = null;

        if ($conversation) {
            // Check authorization
            if ($conversation->mentor_id !== $authUser->id && $conversation->intern_id !== $authUser->id) {
                abort(403, 'Unauthorized access to this conversation.');
            }

            // Mark received unread messages as read
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $authUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $conversation->load(['mentor:id,name,email', 'intern:id,name,email']);
            $conversation->other_user = $conversation->mentor_id === $authUser->id ? $conversation->intern : $conversation->mentor;
            $activeConversation = $conversation;

            $activeMessages = Message::with('sender:id,name')
                ->where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'contacts' => $contacts,
            'activeConversation' => $activeConversation,
            'messages' => $activeMessages,
        ]);
    }

    /**
     * Start/get a conversation with a specific contact user.
     */
    public function startConversation(User $user)
    {
        $authUser = Auth::user();
        if ($user->id === $authUser->id) {
            return redirect()->route('chat.index');
        }

        $conversation = $this->getOrCreateConversation($authUser, $user);

        return redirect()->route('chat.index', ['conversation' => $conversation->id]);
    }

    /**
     * Send message with optional image, video, or file attachment.
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $authUser = Auth::user();

        if ($conversation->mentor_id !== $authUser->id && $conversation->intern_id !== $authUser->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:51200', // 50MB max for videos/files
        ]);

        if (empty($request->message) && !$request->hasFile('file')) {
            return back()->withErrors(['message' => 'Please enter a message or select a file to send.']);
        }

        $filePath = null;
        $fileName = null;
        $type = 'text';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();

            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $type = 'video';
            } else {
                $type = 'file';
            }

            $filePath = $file->store('chat_files', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $authUser->id,
            'message' => $request->message,
            'type' => $type,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'is_read' => false,
        ]);

        $conversation->touch(); // Update updated_at timestamp

        MessageSent::dispatch($message);

        return back();
    }

    /**
     * Fetch latest messages for polling.
     */
    public function getMessages(Conversation $conversation)
    {
        $authUser = Auth::user();

        if ($conversation->mentor_id !== $authUser->id && $conversation->intern_id !== $authUser->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $authUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::with('sender:id,name')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Helper to retrieve or create a conversation between mentor & intern.
     */
    private function getOrCreateConversation(User $user1, User $user2): Conversation
    {
        $mentorId = $user1->role === 'mentor' ? $user1->id : $user2->id;
        $internId = $user1->role === 'intern' ? $user1->id : $user2->id;

        if ($user1->role === $user2->role) {
            // Default fallback if roles are same (e.g. mentor & mentor or admin)
            $mentorId = min($user1->id, $user2->id);
            $internId = max($user1->id, $user2->id);
        }

        return Conversation::firstOrCreate([
            'mentor_id' => $mentorId,
            'intern_id' => $internId,
        ]);
    }
}
