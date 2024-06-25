<?php

namespace Ercogx\FilamentOpenaiAssistant\Pages;

use Ercogx\FilamentOpenaiAssistant\Contracts\OpenaiThreadServicesContract as ThreadServices;
use Ercogx\FilamentOpenaiAssistant\Models\ChatThread;
use Ercogx\FilamentOpenaiAssistant\Services\ChatThreadModelServices;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use OpenAI\Responses\Threads\Messages\ThreadMessageResponse;

class OpenaiAssistantPage extends Page
{
    protected static string $view = 'filament-openai-assistant::pages.assistant-chat';

    #[Locked]
    public array $assistants = [];

    #[Locked]
    public array $threads = [];

    public array $messages = [];

    public string $selectedAssistant;

    public string $selectedThread;

    public string $selectedThreadName;

    public string $message = '';

    public bool $hasMore = false;

    public ?string $lastMessageId = null;

    public function mount(ThreadServices $threadServices): void
    {
        $this->setAssistants();

        $this->setThreads();

        $this->loadMessages();
    }

    public function updatedSelectedAssistant(): void
    {
        throw_unless(isset($this->assistants[$this->selectedAssistant]), 'Unexpected assistant id');

        $this->reset('messages', 'hasMore', 'lastMessageId');

        $this->loadThreads();

        $this->selectedThread = array_key_first($this->threads);

        Cookie::queue('currentAssistant', $this->selectedAssistant, 43800);
        Cookie::queue(Cookie::forget('currentThread'));
    }

    public function updatedSelectedThread(): void
    {
        throw_unless(isset($this->threads[$this->selectedThread]), 'Unexpected thread id');

        $this->setCurrentThreadToCookie();
        $this->selectedThreadName = $this->threads[$this->selectedThread];
        $this->reset('messages', 'hasMore', 'lastMessageId');

        if ($this->selectedThread !== 'new-thread') {
            $this->loadMessages();
        }

        $this->dispatch('chat-updated');
    }

    public function sendMessage(): void
    {
        $this->messages[] = [
            'role' => 'user',
            'text' => $this->message,
        ];

        $this->message = '';

        $this->dispatch('chat-updated');

        $this->dispatch($this->selectedThread === 'new-thread' ? 'create-new-thread' : 'update-thread');
    }

    #[On('create-new-thread')]
    public function createNewThread(ThreadServices $threadServices): void
    {
        $chatThread = $threadServices->create($this->selectedAssistant, auth()->id(), $this->lastMessage());

        $this->messages[] = [
            'role' => 'assistant',
            'text' => $threadServices->getLastMessage($chatThread->thread_id),
        ];

        $this->threads[$chatThread->thread_id] = $chatThread->name;

        $this->selectedThreadName = $chatThread->name;

        $this->setCurrentThreadToCookie();

        $this->dispatch('change-selected-thread', id: $chatThread->thread_id);

        $this->dispatch('chat-updated');
    }

    #[On('change-selected-thread')]
    public function changeSelectedThread(string $id): void
    {
        $this->selectedThread = $id;
    }

    #[On('update-thread')]
    public function updateThread(ThreadServices $threadServices): void
    {
        $this->messages[] = [
            'role' => 'assistant',
            'text' => $threadServices->createMessage(
                $this->selectedAssistant,
                $this->selectedThread,
                $this->lastMessage()
            ),
        ];

        $this->dispatch('chat-updated');
    }

    public function renameCurrentThread(): void
    {
        $this->validate([
            'selectedThreadName' => 'required|string|max:255',
        ]);

        ChatThread::query()
            ->where('thread_id', $this->selectedThread)
            ->where('assistant_id', $this->selectedAssistant)
            ->update([
                'name' => $this->selectedThreadName,
            ]);

        $this->threads[$this->selectedThread] = $this->selectedThreadName;

        $this->dispatch('close-modal', id: 'rename-thread');
    }

    protected function loadMessages(?string $afterMessage = null): void
    {
        if ($this->selectedThread === 'new-thread') {
            return;
        }

        $messages = app(ThreadServices::class)
            ->listMessages($this->selectedThread, $afterMessage);

        $this->hasMore = $messages->hasMore;
        $this->lastMessageId = $messages->lastId;

        $newMessages = collect($messages->data)
            ->map(function (ThreadMessageResponse $message) {
                $simpleMessages = [];

                foreach ($message->content as $content) {
                    if ($content->type === 'text') {
                        $simpleMessages[] = [
                            'role' => $message->role,
                            'text' => $message->content[0]->text->value,
                        ];
                    }
                }

                return $simpleMessages;
            })
            ->collapse()
            ->reverse()
            ->toArray();

        $this->messages = array_merge($newMessages, $this->messages);
    }

    public function loadMoreMessage(): void
    {
        $this->loadMessages($this->lastMessageId);
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-openai-assistant::assistant-page.navigation_label');
    }

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return config('filament-openai-assistant.navigation_icon', 'heroicon-o-chat-bubble-oval-left');
    }

    protected function loadThreads(): void
    {
        $this->threads = ChatThreadModelServices::getChatThreadModel()::query()
            ->where('assistant_id', $this->selectedAssistant)
            ->where('user_id', auth()->id())
            ->pluck('name', 'thread_id')
            ->prepend('New Thread', 'new-thread')
            ->toArray();
    }

    protected function setCurrentThreadToCookie(): void
    {
        Cookie::queue('currentThread', $this->selectedThread, 43800);
    }

    protected function lastMessage()
    {
        return last($this->messages)['text'];
    }

    protected function setAssistants(): void
    {
        $this->assistants = Arr::pluck(config('filament-openai-assistant.assistants'), 'name', 'id');

        $currentAssistant = Cookie::get('currentAssistant');

        $this->selectedAssistant = $currentAssistant && array_key_exists($currentAssistant, $this->assistants)
            ? $currentAssistant
            : array_key_first($this->assistants);
    }

    protected function setThreads(): void
    {
        $this->loadThreads();

        $currentThread = Cookie::get('currentThread');

        $this->selectedThread = $currentThread && array_key_exists($currentThread, $this->threads)
            ? $currentThread
            : array_key_first($this->threads);
    }
}
