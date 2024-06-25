# OpenAI Assistant Integration

Filament OpenAI Assistant is a filament plugin that adds a chat page with an Open AI assistant

Preview:
![](https://raw.githubusercontent.com/ercogx/filament-openai-assistant/main/screenshots/preview.png)
Dark Mode:
![](https://raw.githubusercontent.com/ercogx/filament-openai-assistant/main/screenshots/dark-mode.png)

## Feature

- Integrate with OpenAI Assistant
- Easy to Setup
- Multiple Assistants
- Multiple Threads
- Support for dark mode

## Usage

### Installation

First, you need install the package via composer:

```bash
composer require ercogx/filament-openai-assistant
```

### Publish Migration

Then need publish migration:

```bash
php artisan vendor:publish --tag="filament-openai-assistant-migrations"
```

Optional you can change foreign id for auth user if you use a different model instead of the  ```\App\Models\User::class```

### Publish Config

Next, you can publish the config files with:

```bash
php artisan vendor:publish --tag="filament-openai-assistant-config"
```

This will create a `config/filament-openai-assistant.php` configuration file in your project, which you can modify to your needs using environment variables:

```
OPENAI_API_KEY=sk-***
OPENAI_ASSISTANT_ID=asst_***
OPENAI_ASSISTANT_NAME=Assistant
```

You can also add more assistants to the ```assistants``` array as needed

### Add To Filament Panels

The last step add the Plugin to your Panel's configuration. This will register the plugin's page with the Panel.

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            \Ercogx\FilamentOpenaiAssistant\OpenaiAssistantPlugin::make()
        ]);
}
```

You can also change the chat page to customize it

```php
\Ercogx\FilamentOpenaiAssistant\OpenaiAssistantPlugin::make()
    ->setRegistrablePages([
        \App\Filament\Pages\ChatPage::class
    ])
```

```php
<?php

namespace App\Filament\Pages;

use Ercogx\FilamentOpenaiAssistant\Pages\OpenaiAssistantPage;

class ChatPage extends OpenaiAssistantPage
{
    
}
```

## Additional steps

### Views

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-openai-assistant-views"
```

### Translations

Optionally, you can publish the translations using

```bash
php artisan vendor:publish --tag="filament-openai-assistant-translations"
```

### Custom Thread Chat Model

If you want to use your own model for chat thread you need call ```useChatThreadModel``` in boot method of any service provider

```php
public function boot(): void
{
    \Ercogx\FilamentOpenaiAssistant\Services\ChatThreadModelServices::useChatThreadModel(\App\Models\MyChatThread::class);
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ercogx](https://github.com/ercogx)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
