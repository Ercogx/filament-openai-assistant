<?php

namespace Ercogx\FilamentOpenaiAssistant\Services;

use Ercogx\FilamentOpenaiAssistant\Contracts\OpenaiThreadServicesContract;
use Ercogx\FilamentOpenaiAssistant\Models\ChatThread;
use Illuminate\Support\Str;
use OpenAI\Client;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;

class OpenaiThreadServices implements OpenaiThreadServicesContract
{
    private Client $openaiClient;

    public function __construct()
    {
        if (! $openaiKey = config('filament-openai-assistant.openai.api_key')) {
            throw new \Exception('API_KEY Missing!');
        }

        $this->openaiClient = \OpenAI::client($openaiKey);
    }

    public function create(string $assistantId, string | int $userId, string $message): ChatThread
    {
        $response = $this->openaiClient->threads()->createAndRun([
            'assistant_id' => $assistantId,
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
            ],
        ]);

        $this->waitForRunIsComplete($response);

        return ChatThreadModelServices::getChatThreadModel()::create([
            'assistant_id' => $assistantId,
            'thread_id' => $response->threadId,
            'user_id' => $userId,
            'name' => Str::limit($message, 50),
        ]);
    }

    public function listMessages(string $threadId, ?string $after = null): ThreadMessageListResponse
    {
        return $this->openaiClient->threads()->messages()->list($threadId, array_filter([
            'after' => $after,
            'limit' => config('filament-openai-assistant.message_request_limit'),
        ]));
    }

    public function createMessage(string $assistantId, string $threadId, string $message): string
    {
        $this->openaiClient->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $message,
        ]);

        $response = $this->openaiClient->threads()->runs()->create($threadId, [
            'assistant_id' => $assistantId,
        ]);

        $this->waitForRunIsComplete($response);

        return $this->getLastMessage($threadId);
    }

    public function getLastMessage(string $threadId): string
    {
        return $this->listMessages($threadId)->data[0]->content[0]->text->value;
    }

    protected function waitForRunIsComplete(ThreadRunResponse $response): ThreadRunResponse
    {
        while ($response->status === 'in_progress' || $response->status === 'queued') {
            sleep(1);

            $response = $this->openaiClient->threads()->runs()->retrieve($response->threadId, $response->id);
        }

        return $response;
    }
}
