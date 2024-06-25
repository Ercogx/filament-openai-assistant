<?php

namespace Ercogx\FilamentOpenaiAssistant\Services;

use Ercogx\FilamentOpenaiAssistant\Models\ChatThread;

class ChatThreadModelServices
{
    private static string $chatThreadModel = ChatThread::class;

    /**
     * @return class-string<ChatThread>
     */
    public static function getChatThreadModel(): string
    {
        return self::$chatThreadModel;
    }

    public static function useChatThreadModel(string $chatThreadModel): void
    {
        self::$chatThreadModel = $chatThreadModel;
    }
}
