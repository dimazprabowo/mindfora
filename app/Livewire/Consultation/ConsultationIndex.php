<?php

namespace App\Livewire\Consultation;

use App\Events\NewChatMessage;
use App\Events\UserTyping;
use App\Livewire\Traits\HasNotification;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Traits\HasDynamicLike;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;

class ConsultationIndex extends Component
{
    use AuthorizesRequests, HasDynamicLike, HasNotification;

    public ?int $activeChatId = null;

    public string $messageBody = '';

    public string $searchCounselor = '';

    /** @var array|null Cached chat IDs for current user */
    private ?array $userChatIds = null;

    public function mount(): void
    {
        $this->authorize('consultation_start');

        // Auto-select the most recent consultation chat
        $latestChat = $this->getUserChats()->first();
        if ($latestChat) {
            $this->activeChatId = $latestChat->id;
            $this->markChatAsRead();
        }
    }

    public function selectChat(int $chatId): void
    {
        if (! $this->isUserChat($chatId)) {
            return;
        }

        $this->activeChatId = $chatId;
        $this->markChatAsRead();
        $this->messageBody = '';
    }

    public function startConsultation(int $counselorId): void
    {
        $me = Auth::id();

        if ($counselorId === $me) {
            return;
        }

        // Verify target is admin/super-admin
        $counselor = User::findOrFail($counselorId);
        if (! $counselor->hasRole(['super-admin', 'admin'])) {
            $this->notifyError('User tersebut bukan konselor/admin.');

            return;
        }

        $chat = Chat::findDirectChat($me, $counselorId);

        if (! $chat) {
            $chat = Chat::create([
                'is_group' => false,
                'created_by' => $me,
            ]);
            $chat->participants()->attach([$me, $counselorId]);
            $this->userChatIds = null;
        } else {
            // Re-open if previously deleted
            DB::table('chat_participants')
                ->where('chat_id', $chat->id)
                ->where('user_id', $me)
                ->whereNotNull('deleted_at')
                ->update(['deleted_at' => now()]);
            $this->userChatIds = null;
        }

        $this->activeChatId = $chat->id;
        $this->searchCounselor = '';
        $this->markChatAsRead();
        $this->notifySuccess('Konsultasi dimulai! Silakan tulis pesan Anda.');
    }

    public function sendMessage(): void
    {
        $body = trim($this->messageBody);

        if (empty($body) || ! $this->activeChatId) {
            return;
        }

        if (! $this->isUserChat($this->activeChatId)) {
            return;
        }

        $message = ChatMessage::create([
            'chat_id' => $this->activeChatId,
            'user_id' => Auth::id(),
            'body' => $body,
            'type' => 'text',
        ]);

        DB::table('chat_participants')
            ->where('chat_id', $this->activeChatId)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        try {
            broadcast(new NewChatMessage($message->load('user')))->toOthers();
        } catch (Throwable) {
            // Broadcast failed — message still saved
        }

        $this->messageBody = '';
    }

    public function sendTyping(bool $isTyping = true): void
    {
        if (! $this->activeChatId) {
            return;
        }

        try {
            broadcast(new UserTyping(
                chatId: $this->activeChatId,
                userId: Auth::id(),
                userName: Auth::user()->name,
                isTyping: $isTyping,
            ))->toOthers();
        } catch (Throwable) {
            // Silently ignore
        }
    }

    public function markChatAsRead(): void
    {
        if (! $this->activeChatId) {
            return;
        }

        DB::table('chat_participants')
            ->where('chat_id', $this->activeChatId)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);
    }

    public function getListeners(): array
    {
        $userId = Auth::id();

        return [
            "echo-private:user.{$userId},NewChatMessage" => 'handleNewMessage',
            "echo-private:user.{$userId},UserTyping" => 'handleTyping',
        ];
    }

    public function handleNewMessage($event): void
    {
        if (isset($event['chat_id']) && $event['chat_id'] == $this->activeChatId) {
            $this->markChatAsRead();
        }
    }

    public function handleTyping($event): void
    {
        $this->dispatch('user-typing', userId: $event['user_id'], userName: $event['user_name'], isTyping: $event['is_typing']);
    }

    private function isUserChat(int $chatId): bool
    {
        if ($this->userChatIds === null) {
            $this->userChatIds = DB::table('chat_participants')
                ->where('user_id', Auth::id())
                ->pluck('chat_id')
                ->all();
        }

        return in_array($chatId, $this->userChatIds);
    }

    /**
     * Get user's consultation chats (only direct chats with admin/counselor).
     */
    private function getUserChats()
    {
        $userId = Auth::id();

        $participantData = DB::table('chat_participants')
            ->where('user_id', $userId)
            ->get(['chat_id', 'last_read_at', 'deleted_at']);

        $chats = Chat::whereIn('id', $participantData->pluck('chat_id'))
            ->where('is_group', false)
            ->with(['participants', 'latestMessage.user'])
            ->get()
            ->filter(function ($chat) use ($participantData) {
                $deletedAt = $participantData->firstWhere('chat_id', $chat->id)?->deleted_at;
                if (! $deletedAt) {
                    return true;
                }

                return $chat->latestMessage && $chat->latestMessage->created_at > Carbon::parse($deletedAt);
            })
            ->sortByDesc(fn ($chat) => $chat->latestMessage?->created_at ?? $chat->created_at);

        return $chats;
    }

    public function render()
    {
        $userId = Auth::id();
        $chats = $this->getUserChats();

        $participantData = DB::table('chat_participants')
            ->where('user_id', $userId)
            ->get(['chat_id', 'last_read_at', 'deleted_at']);

        $lastReadMap = $participantData->pluck('last_read_at', 'chat_id');
        $deletedAtMap = $participantData->pluck('deleted_at', 'chat_id');

        // Unread counts
        $chatIds = $chats->pluck('id');
        $unreadCounts = [];
        if ($chatIds->isNotEmpty()) {
            $unreadQuery = DB::table('chat_messages')
                ->select('chat_id', DB::raw('COUNT(*) as unread'))
                ->whereIn('chat_id', $chatIds)
                ->where('user_id', '!=', $userId);

            $unreadQuery->where(function ($q) use ($chatIds, $lastReadMap, $deletedAtMap) {
                foreach ($chatIds as $chatId) {
                    $lastRead = $lastReadMap[$chatId] ?? null;
                    $deletedAt = $deletedAtMap[$chatId] ?? null;

                    $q->orWhere(function ($sub) use ($chatId, $lastRead, $deletedAt) {
                        $sub->where('chat_id', $chatId);
                        if ($lastRead) {
                            $sub->where('created_at', '>', $lastRead);
                        }
                        if ($deletedAt) {
                            $sub->where('created_at', '>', $deletedAt);
                        }
                    });
                }
            });

            $unreadCounts = $unreadQuery->groupBy('chat_id')
                ->pluck('unread', 'chat_id')
                ->all();
        }

        // Active chat messages
        $activeChat = null;
        $messages = collect();

        if ($this->activeChatId) {
            $participant = $participantData->firstWhere('chat_id', $this->activeChatId);

            if ($participant) {
                $activeChat = $chats->firstWhere('id', $this->activeChatId);

                if (! $activeChat) {
                    $activeChat = Chat::with(['participants', 'latestMessage.user'])
                        ->find($this->activeChatId);
                }

                if ($activeChat) {
                    $messages = ChatMessage::where('chat_id', $this->activeChatId)
                        ->with(['user'])
                        ->when($participant->deleted_at, fn ($q) => $q->where('created_at', '>', $participant->deleted_at))
                        ->orderBy('created_at', 'asc')
                        ->limit(100)
                        ->get();
                }
            }
        }

        // Available counselors (admin/super-admin)
        $counselors = collect();
        if (strlen($this->searchCounselor) >= 1) {
            $operator = $this->getLikeOperator();
            $counselors = User::active()
                ->where('id', '!=', $userId)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
                ->where(function ($q) use ($operator) {
                    $q->where('name', $operator, "%{$this->searchCounselor}%")
                        ->orWhere('email', $operator, "%{$this->searchCounselor}%");
                })
                ->limit(10)
                ->get();
        } else {
            // Show all available counselors by default
            $counselors = User::active()
                ->where('id', '!=', $userId)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
                ->limit(10)
                ->get();
        }

        return view('livewire.consultation.consultation-index', [
            'chats' => $chats,
            'activeChat' => $activeChat,
            'messages' => $messages,
            'counselors' => $counselors,
            'unreadCounts' => $unreadCounts,
        ]);
    }
}
