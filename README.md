# chamber-orchestra/telegram-bundle

Symfony 8 bundle for building Telegram bots using HttpKernel sub-request routing,
a view/keyboard DSL, and async webhook processing via Symfony Messenger.

## Installation

```bash
composer require chamber-orchestra/telegram-bundle
```

## Configuration

```yaml
# config/packages/telegram.yaml
chamber_orchestra_telegram:
    token: '%env(TELEGRAM_TOKEN)%'
    webhook_path: /telegram/webhook
    allowed_telegrams: []   # optional: debug-mode whitelist of Telegram user IDs
```

Register the webhook route:

```yaml
# config/routes/telegram.yaml
chamber_orchestra_telegram:
    resource: '@ChamberOrchestraTelegramBundle/Resources/config/routes.php'
    prefix: /
```

Configure Messenger transports:

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            webhook: '%env(RABBITMQ_DSN)%'
        routing:
            'ChamberOrchestra\TelegramBundle\Messenger\Message\TelegramWebhookMessage': webhook
            'ChamberOrchestra\TelegramBundle\Messenger\Message\SendMessage': webhook
```

## How It Works

```
POST /telegram/webhook
  → WebhookController dispatches TelegramWebhookMessage to queue
  → TelegramWebhookHandler (async worker)
      → TelegramRequestFactory maps payload to a synthetic Request
      → HttpKernelInterface::handle($request, SUB_REQUEST)
          → Symfony routing matches #[Route] on your action controller
          → Optional: EntityValueResolver / TelegramUserValueResolver resolve arguments
          → Controller returns ViewInterface
          → TelegramViewSubscriber (kernel.view) sends reply via Telegram API
```

### URL Scheme

| Telegram update | Synthetic URL | Method |
|---|---|---|
| `/command` text | `/telegram/cmd/command` | GET |
| Plain text (no pending state) | `/telegram/message` | GET |
| Plain text (pending state `foo`) | `/telegram/input/foo` | GET |
| Callback query with `path` key | `/telegram/callback/{path}` | POST |
| `my_chat_member` update | `/telegram/member/{status}` | GET |

### Request Attributes

| Attribute | Type | Description |
|---|---|---|
| `_chat_id` | string | Chat ID to reply to |
| `_telegram_user_id` | string | Sender's Telegram user ID |
| `_telegram_payload` | array | Full raw webhook payload |
| `_message_text` | string | Raw text of the message (message routes only) |
| `_callback_data` | array | Decoded callback data (callback routes only) |
| `_callback_query_id` | string | Callback query ID (callback routes only) |
| `_routed_pending_route` | string\|null | Pending route used for this request (input routes only) |

## Usage

### Creating an Action

Create a regular Symfony controller with a `#[Route]` attribute and return a `ViewInterface`:

```php
use ChamberOrchestra\TelegramBundle\Contracts\View\ViewInterface;
use ChamberOrchestra\TelegramBundle\View\TextView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/telegram/cmd/hello', name: 'telegram_cmd_hello')]
class HelloAction
{
    public function __invoke(Request $request): ViewInterface
    {
        return new TextView('Hello! 👋');
    }
}
```

Register as a public controller service in your `config/services/telegram.yaml`:

```yaml
services:
    Telegram\Action\:
        resource: '../../src/Telegram/Action'
        public: true
        tags: ['controller.service_arguments']
```

Or use the Maker:

```bash
php bin/console make:telegram:handler
```

### Automatic Entity Resolution

Because Telegram actions run through the real HttpKernel, Symfony's `EntityValueResolver`
works out of the box. Put the entity ID in the URL path and declare it as an argument:

```php
// Callback data: {"path": "note/42"}
// → TelegramRequestFactory creates: POST /telegram/callback/note/42
// → EntityValueResolver: NoteRepository::find(42) → Note $note

#[Route('/telegram/callback/note/{id}', name: 'telegram_callback_note_view', methods: ['POST'])]
class NoteViewCallbackAction
{
    public function __invoke(Request $request, Note $note): ViewInterface
    {
        return new TextView("<b>{$note->getTitle()}</b>\n\n{$note->getBody()}");
    }
}
```

No `#[MapEntity]` needed — `{id}` is the standard Symfony convention.

### User Resolution

Implement `TelegramUserProviderInterface` to let the bundle resolve your User entity
from the incoming Telegram user ID:

```php
use ChamberOrchestra\TelegramBundle\Contracts\User\TelegramUserProviderInterface;

class UserTelegramProvider implements TelegramUserProviderInterface
{
    public function loadByTelegramId(string $telegramUserId): ?User
    {
        return $this->repository->findOneByTelegramId($telegramUserId);
    }
}
```

Declare the alias in your service config:

```yaml
ChamberOrchestra\TelegramBundle\Contracts\User\TelegramUserProviderInterface:
    alias: App\User\Provider\UserTelegramProvider
```

Then declare `?User $user` in your controller — the bundle resolves it automatically:

```php
#[Route('/telegram/cmd/profile', name: 'telegram_cmd_profile')]
class ProfileAction
{
    public function __invoke(Request $request, ?User $user): ViewInterface
    {
        if ($user === null) {
            return new TextView('Please /start first.');
        }

        return new TextView("Hello, {$user->getFirstName()}!");
    }
}
```

### Views

| View | Telegram method |
|---|---|
| `TextView($html)` | `sendMessage` |
| `ImageView($file, $caption)` | `sendPhoto` |
| `VideoView($file, $caption)` | `sendVideo` |
| `DocumentView($file, $caption)` | `sendDocument` |
| `VideoNoteView($file)` | `sendVideoNote` |
| `MediaGroupView($items)` | `sendMediaGroup` (synchronous) |
| `LinkView($text, $title, $url)` | `sendMessage` + inline URL button |
| `LoginLinkView($text, $title, $url)` | `sendMessage` + login_url button |
| `CollectionView($views)` | Multiple messages sequentially |

### Inline Keyboard

```php
use ChamberOrchestra\TelegramBundle\Model\CallbackOption;
use ChamberOrchestra\TelegramBundle\Model\LinkOption;
use ChamberOrchestra\TelegramBundle\Model\OptionsCollection;
use ChamberOrchestra\TelegramBundle\View\TextView;

$view = (new TextView('Choose an option:'))
    ->addInlineKeyboardCollection(
        (new OptionsCollection())
            ->row([
                new CallbackOption('Option A', ['path' => 'my-action', 'value' => 'a']),
                new CallbackOption('Option B', ['path' => 'my-action', 'value' => 'b']),
            ])
            ->add(new LinkOption('Open Google', 'https://google.com'))
    );
```

Callback data must include a `path` key — it becomes the URL path:
`{"path": "my-action", "value": "a"}` → `POST /telegram/callback/my-action`.

### Events

| Event | When | Useful for |
|---|---|---|
| `TelegramRequestEvent` | Before sub-request | Persisting `BotRequest` |
| `TelegramMessageSentEvent` | After successful API call | Persisting `BotResponse` |

```php
use ChamberOrchestra\TelegramBundle\Event\TelegramRequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(TelegramRequestEvent::class)]
class PersistBotRequestListener
{
    public function __invoke(TelegramRequestEvent $event): void
    {
        // $event->payload — raw webhook array
        // $event->userId  — Telegram user ID string
    }
}
```

### Conversation State (Multi-step Input)

For flows that require multiple sequential inputs (e.g. ask for date of birth, then city),
implement `TelegramConversationStateInterface` and register it as a service alias:

```php
use ChamberOrchestra\TelegramBundle\Contracts\Conversation\TelegramConversationStateInterface;

class RedisConversationState implements TelegramConversationStateInterface
{
    public function getPendingRoute(string $telegramUserId): ?string { ... }
    public function setPendingRoute(string $telegramUserId, string $route): void { ... }
    public function clearPendingRoute(string $telegramUserId): void { ... }
}
```

```yaml
ChamberOrchestra\TelegramBundle\Contracts\Conversation\TelegramConversationStateInterface:
    alias: App\Telegram\Conversation\RedisConversationState
```

**How it works:**

1. An action sets a pending route: `$state->setPendingRoute($userId, 'waiting-dob')`
2. The user sends a plain text message
3. `TelegramRequestFactory` reads the state and routes to `/telegram/input/waiting-dob`
4. After a successful response, `TelegramViewSubscriber` auto-clears the state
5. If the action sets a new pending route during execution, the state is preserved (multi-step)
6. If validation fails, `TelegramExceptionSubscriber` sends the error and state is NOT cleared — the user retries

**Example — ask for date of birth:**

```php
// Step 1: ask
#[Route('/telegram/cmd/ask-dob')]
class AskDobAction
{
    public function __invoke(Request $request): ViewInterface
    {
        $this->state->setPendingRoute($this->getTelegramUserId($request), 'waiting-dob');
        return new TextView('Please enter your date of birth (DD.MM.YYYY):');
    }
}

// Step 2: receive and validate
#[Route('/telegram/input/waiting-dob')]
class WaitingDobAction
{
    public function __invoke(Request $request, #[TelegramText] DobDto $dto): ViewInterface
    {
        // State is auto-cleared on success. $dto->date is a valid DateTimeImmutable.
        return new TextView('Saved: ' . $dto->date->format('d.m.Y'));
    }
}

// Cancel at any time
#[Route('/telegram/cmd/cancel')]
class CancelAction
{
    public function __invoke(Request $request): ViewInterface
    {
        $this->state->clearPendingRoute($this->getTelegramUserId($request));
        return new TextView('Cancelled.');
    }
}
```

### Text Input DTOs

Use `#[TelegramText]` to map the incoming message text to a typed DTO.
Extend `AbstractTelegramDto` and declare `constraints()` + `transform()`:

```php
use ChamberOrchestra\TelegramBundle\Attribute\TelegramText;
use ChamberOrchestra\TelegramBundle\Dto\AbstractTelegramDto;
use Symfony\Component\Validator\Constraints as Assert;

class DobDto extends AbstractTelegramDto
{
    public readonly \DateTimeImmutable $date;

    public static function constraints(): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotBlank(message: 'Please enter a date.'),
                new Assert\Regex(pattern: '/^\d{2}\.\d{2}\.\d{4}$/', message: 'Format: DD.MM.YYYY'),
            ]),
        ];
    }

    protected function transform(string $raw): void
    {
        $this->date = \DateTimeImmutable::createFromFormat('d.m.Y', $raw);
    }
}

// In your action:
public function __invoke(Request $request, #[TelegramText] DobDto $dto): ViewInterface
```

The resolver validates the raw string against `constraints()` first.
If validation fails, `TelegramValidationException` is thrown — the error message is sent
to the user and the conversation state is preserved for retry.
`transform()` is only called with a valid string.

For plain string access without validation:

```php
public function __invoke(Request $request, #[TelegramText] string $text): ViewInterface
```

### send() vs doSend()

`Telegram::send()` dispatches a `SendMessage` to the queue (non-blocking).
`SendMessageHandler` applies rate limiting (29 req/s) and calls `doSend()` (blocking HTTP call).

`MediaGroupView` is always sent synchronously via `multipart()` because `DataPart`
objects cannot be serialized for the queue.

## Requirements

- PHP 8.4+
- Symfony 8.0+
- Symfony Messenger with an async transport (AMQP recommended)

## License

Apache-2.0. See [LICENSE](LICENSE).
