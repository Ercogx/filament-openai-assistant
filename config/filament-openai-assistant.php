<?php

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'assistants' => [
        'my_assistant' => [
            'id' => env('OPENAI_ASSISTANT_ID'),
            'name' => env('OPENAI_ASSISTANT_NAME', 'Assistant'),
        ],
    ],

    'message_request_limit' => env('OPENAI_ASSISTANT_MESSAGE_REQUEST_LIMIT', 20),

    'navigation_icon' => 'heroicon-o-chat-bubble-oval-left',
];
