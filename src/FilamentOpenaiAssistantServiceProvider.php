<?php

namespace Ercogx\FilamentOpenaiAssistant;

use Ercogx\FilamentOpenaiAssistant\Contracts\OpenaiThreadServicesContract;
use Ercogx\FilamentOpenaiAssistant\Services\OpenaiThreadServices;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentOpenaiAssistantServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-openai-assistant')
            ->hasMigration('2024_06_22_175821_create_chat_threads_table')
            ->runsMigrations()
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted()
    {
        FilamentAsset::register([
            Css::make('filament-openai-assistant', __DIR__ . '/../resources/dist/filament-openai-assistant.css')->loadedOnRequest(),
        ], package: 'ercogx/filament-openai-assistant');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpenaiThreadServicesContract::class, OpenaiThreadServices::class);
    }
}
