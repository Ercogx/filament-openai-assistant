<?php

namespace Ercogx\FilamentOpenaiAssistant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatThread extends Model
{
    protected $guarded = [
        'id',
        'updated_at',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('filament-openai-assistant.user_model'));
    }
}
