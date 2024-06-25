<?php

namespace Ercogx\FilamentOpenaiAssistant;

use Ercogx\FilamentOpenaiAssistant\Pages\OpenaiAssistantPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

class OpenaiAssistantPlugin implements Plugin
{
    private array $registrablePages = [
        OpenaiAssistantPage::class,
    ];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'openai-assistant';
    }

    public function register(Panel $panel): void
    {
        $panel->pages($this->getRegistrablePages());
    }

    public function boot(Panel $panel): void {}

    private function getRegistrablePages(): array
    {
        return $this->registrablePages;
    }

    public function setRegistrablePages(array $registrablePages): OpenaiAssistantPlugin
    {
        $this->registrablePages = $registrablePages;

        return $this;
    }
}
