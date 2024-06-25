<?php

namespace Ercogx\FilamentOpenaiAssistant\Contracts;

use Ercogx\FilamentOpenaiAssistant\Models\ChatThread;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;

interface OpenaiThreadServicesContract
{
    public function create(string $assistantId, string | int $userId, string $message): ChatThread;

    public function listMessages(string $threadId, ?string $after = null): ThreadMessageListResponse;

    public function createMessage(string $assistantId, string $threadId, string $message): string;

    public function getLastMessage(string $threadId): string;
}
