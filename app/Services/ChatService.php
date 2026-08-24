<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class ChatService
{
    /**
     * Get or create a conversation between two users.
     */
    public function getOrCreateConversation(User $user1, User $user2): Conversation
    {
        $mentorId = $user1->role === UserRole::MENTOR->value ? $user1->id : $user2->id;
        $internId = $user1->role === UserRole::INTERN->value ? $user1->id : $user2->id;

        if ($user1->role === $user2->role) {
            $mentorId = min($user1->id, $user2->id);
            $internId = max($user1->id, $user2->id);
        }

        return Conversation::firstOrCreate([
            'mentor_id' => $mentorId,
            'intern_id' => $internId,
        ]);
    }

    /**
     * Get all conversations for a user with unread counts and partner users mapped.
     */
    public function getUserConversations(User $authUser): Collection
    {
        return Conversation::with(['mentor:id,name,email', 'intern:id,name,email', 'latestMessage'])
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
    }

    /**
     * Get contact users eligible to initiate chat with the authenticated user.
     */
    public function getAvailableContacts(User $authUser): Collection
    {
        return User::where('id', '!=', $authUser->id)
            ->when($authUser->role === UserRole::MENTOR->value, fn($q) => $q->where('role', UserRole::INTERN->value))
            ->when($authUser->role === UserRole::INTERN->value, fn($q) => $q->where('role', UserRole::MENTOR->value))
            ->select('id', 'name', 'email', 'role')
            ->get();
    }

    /**
     * Mark unread messages in a conversation as read.
     */
    public function markMessagesAsRead(int $conversationId, int $currentUserId): void
    {
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Fetch conversation messages sorted chronologically.
     */
    public function getConversationMessages(int $conversationId): Collection
    {
        return Message::with('sender:id,name')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Send a new message with optional file attachment.
     */
    public function sendMessage(Conversation $conversation, User $sender, ?string $textMessage, ?UploadedFile $file): Message
    {
        $filePath = null;
        $fileName = null;
        $type = 'text';

        if ($file) {
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
            'sender_id' => $sender->id,
            'message' => $textMessage,
            'type' => $type,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'is_read' => false,
        ]);

        $conversation->touch();

        MessageSent::dispatch($message);

        return $message;
    }
}
