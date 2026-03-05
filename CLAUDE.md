# chamber-orchestra/telegram-bundle

Symfony 8 bundle for building Telegram bots — attribute-based routing, filter system, view/keyboard DSL, async webhook processing.

## Commands

```bash
# Tests
vendor/bin/phpunit                      # Run all bundle tests
vendor/bin/phpunit tests/SomeTest.php   # Run a single test file

# Code style
vendor/bin/php-cs-fixer fix             # Fix code style
vendor/bin/php-cs-fixer fix --dry-run   # Check only

# Static analysis
vendor/bin/phpstan analyse              # Run PHPStan

# Scaffold a new handler (run from the host project)
php bin/console make:telegram:handler
```

## Architecture

### Request Flow

```
POST /telegram/webhook
  → WebhookController (dispatches TelegramWebhookMessage to AMQP)
  → TelegramWebhookHandler (async)
      → dispatches TelegramRequestEvent
      → ActionHandlerResolver (matches via #[TelegramRoute] + Filter)
      → dispatches TelegramActionResolvedEvent
      → ConcreteHandler::__invoke(AbstractData $dto)
          → View::render() → Telegram::send() → Telegram API
          → dispatches TelegramMessageSentEvent
```

### Adding a Handler

```php
#[TelegramRoute(new TextFilter('/hello'))]
class HelloHandler extends AbstractActionHandler
{
    public function __invoke(AbstractData $dto): void
    {
        $this->renderer->render(new TextView('Hello!'), $dto->getId());
    }
}
```

Tag the service as `telegram.action.handler` (done automatically via `instanceof` rule).

### Key Classes

| Class | Description |
|---|---|
| `AbstractActionHandler` | Base for all handlers; provides `$telegram`, `$renderer`, `$bus`, `$dispatcher`, `$logger` via `#[Required]` setters |
| `#[TelegramRoute]` | Attribute accepting one or an array of `FilterInterface` |
| `TextFilter` | Matches `message.text` case-insensitively |
| `CallbackFilter` | Matches `callback_query.data` JSON key/value |
| `DataFactory` | Maps raw webhook payload → typed DTO (`MessageData`, `CallbackQueryData`, etc.) |
| `ActionHandlerResolver` | Iterates tagged handlers, tests filters, falls back to `FallbackHandler` |
| `Telegram` | HTTP client wrapper; routes to async queue or synchronous multipart |
| `MessageRenderer` | Calls `Telegram::send()` with view data |
| `TextView` | Plain/HTML message |
| `ImageView` / `VideoView` / `DocumentView` | File uploads (async via path) |
| `MediaGroupView` | Multi-file group (synchronous, uses `DataPart`) |
| `LinkView` / `LoginLinkView` | Inline buttons with URL / login_url |
| `CollectionView` | Multiple messages in one handler call |

### Events

| Event | When |
|---|---|
| `TelegramRequestEvent` | Before handler resolution (use to persist `BotRequest`) |
| `TelegramActionResolvedEvent` | After resolver picks the handler |
| `TelegramMessageSentEvent` | After successful Telegram API call (use to persist `BotResponse`) |

### Bundle Configuration

```yaml
# config/packages/telegram.yaml
chamber_orchestra_telegram:
    token: '%env(TELEGRAM_TOKEN)%'
    allowed_telegrams: []   # debug-mode user ID whitelist
    webhook_path: /telegram/webhook
```

## File Structure

```
src/
  Attribute/TelegramRoute.php
  Client/Telegram.php
  Contracts/Handler/HandlerInterface.php
  Controller/WebhookController.php
  DependencyInjection/
  Event/
  Exception/
  Filter/TextFilter.php, CallbackFilter.php
  Form/Data/          ← DTOs (MessageData, CallbackQueryData, …)
  Handler/AbstractActionHandler.php, FallbackHandler.php
  Helper/MessageRenderer.php, ViewHelper.php, MessageHelper.php
  Maker/MakeTelegramHandler.php
  Messenger/Handler/TelegramWebhookHandler.php, SendMessageHandler.php
  Messenger/Message/TelegramWebhookMessage.php, SendMessage.php
  Model/              ← OptionsCollection, CallbackOption, LinkOption, …
  Resolver/ActionHandlerResolver.php
  View/               ← TextView, ImageView, VideoView, …
tests/
  Fixtures/Payloads.php
  Filter/
  Form/Data/
  Messenger/Handler/
  Resolver/
  View/
```
