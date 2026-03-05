# chamber-orchestra/telegram-bundle

Symfony 8 bundle for building Telegram bots — HttpKernel sub-request routing, view/keyboard DSL, async webhook processing.

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

# Scaffold a new action controller (run from the host project)
php bin/console make:telegram:handler
```

## Architecture

### Request Flow

```
POST /telegram/webhook
  → WebhookController (dispatches TelegramWebhookMessage to AMQP)
  → TelegramWebhookHandler (async)
      → dispatches TelegramRequestEvent
      → TelegramRequestFactory::createFromPayload($payload)
          → maps to fake URL: /telegram/cmd/start, /telegram/callback/path, etc.
      → HttpKernelInterface::handle($request, SUB_REQUEST)
          → Symfony routing matches route attribute on action controller
          → TelegramUserValueResolver resolves User argument (optional)
          → controller returns ViewInterface
          → TelegramViewSubscriber (kernel.view, priority 10) renders via Telegram API
```

### Adding a Handler

```php
#[Route('/telegram/cmd/hello', name: 'telegram_cmd_hello')]
class HelloAction
{
    public function __invoke(Request $request): ViewInterface
    {
        return new TextView('Hello!');
    }
}
```

Register in your `config/services/telegram.yaml` as a public controller (or use autoconfiguration).

### URL Scheme

| Trigger | URL | Method |
|---|---|---|
| `/command` text message | `/telegram/cmd/command` | GET |
| Plain text (no slash) | `/telegram/message` | GET |
| Callback query with `path` key | `/telegram/callback/{path}` | POST |
| `my_chat_member` update | `/telegram/member/{status}` | GET |

### Request Attributes (available in all controllers)

| Attribute | Type | Description |
|---|---|---|
| `_chat_id` | string | Chat ID to reply to |
| `_telegram_user_id` | string | Sender's Telegram user ID |
| `_telegram_payload` | array | Full raw webhook payload |
| `_callback_data` | array | Decoded callback data (callback routes only) |
| `_callback_query_id` | string | Callback query ID (callback routes only) |

### Key Classes

| Class | Description |
|---|---|
| `TelegramRequestFactory` | Maps raw payload → synthetic `Request` with fake URL |
| `TelegramUserValueResolver` | Resolves a User entity argument via `TelegramUserProviderInterface` |
| `TelegramViewSubscriber` | `kernel.view` listener (priority 10); renders `ViewInterface` via Telegram API |
| `TelegramExceptionSubscriber` | `kernel.exception` listener; prevents sub-request exceptions from crashing the worker |
| `Telegram` | HTTP client wrapper; routes to async queue or synchronous multipart |
| `MessageRenderer` | Calls `Telegram::send()` with view data |
| `TextView` | Plain/HTML message |
| `ImageView` / `VideoView` / `DocumentView` | File uploads |
| `MediaGroupView` | Multi-file album (synchronous) |
| `LinkView` / `LoginLinkView` | Inline buttons with URL / login_url |
| `CollectionView` | Multiple messages — `TelegramViewSubscriber` iterates its views |

### User Resolution

Implement `TelegramUserProviderInterface` to let the bundle auto-resolve your User entity:

```php
// In your app:
class UserTelegramProvider implements TelegramUserProviderInterface
{
    public function loadByTelegramId(string $telegramUserId): ?User
    {
        return $this->repository->findOneByTelegramId($telegramUserId);
    }
}
```

Then declare the argument in your controller:

```php
public function __invoke(Request $request, ?User $user): ViewInterface { … }
```

### Events

| Event | When |
|---|---|
| `TelegramRequestEvent` | Before sub-request (use to persist `BotRequest`) |
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
  Client/Telegram.php, TelegramClient.php
  Contracts/Token/TokenProviderInterface.php
  Contracts/User/TelegramUserProviderInterface.php
  Contracts/View/ViewInterface.php
  Controller/WebhookController.php
  DependencyInjection/
  Event/TelegramRequestEvent.php, TelegramMessageSentEvent.php
  EventSubscriber/TelegramViewSubscriber.php, TelegramExceptionSubscriber.php
  Exception/
  Http/TelegramRequestFactory.php, TelegramUserValueResolver.php
  Maker/MakeTelegramHandler.php
  Messenger/Handler/TelegramWebhookHandler.php, SendMessageHandler.php
  Messenger/Message/TelegramWebhookMessage.php, SendMessage.php
  Model/OptionsCollection.php, CallbackOption.php, LinkOption.php, …
  View/TextView.php, ImageView.php, VideoView.php, CollectionView.php, …
tests/
  Fixtures/Payloads.php
  Http/TelegramRequestFactoryTest.php
  Messenger/Handler/TelegramWebhookHandlerTest.php
  View/
```
