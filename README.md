# chamber-orchestra/telegram-bundle

Symfony 8 bundle for building Telegram bots with attribute-based routing, a filter system, a view/keyboard DSL, and async webhook processing via Symfony Messenger.

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
    allowed_telegrams: []          # optional: debug-mode whitelist of user IDs
```

Register the webhook route:

```yaml
# config/routes/telegram.yaml
chamber_orchestra_telegram:
    resource: '@ChamberOrchestraTelegramBundle/Resources/config/routes.php'
    prefix: /
```

Configure the Messenger transport:

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

## Usage

### Creating a Handler

```php
use ChamberOrchestra\TelegramBundle\Attribute\TelegramRoute;
use ChamberOrchestra\TelegramBundle\Filter\TextFilter;
use ChamberOrchestra\TelegramBundle\Form\Data\AbstractData;
use ChamberOrchestra\TelegramBundle\Handler\AbstractActionHandler;
use ChamberOrchestra\TelegramBundle\View\TextView;

#[TelegramRoute(new TextFilter('/start'))]
class StartHandler extends AbstractActionHandler
{
    public function __invoke(AbstractData $dto): void
    {
        $this->renderer->render(new TextView('Hello! 👋'), $dto->getId());
    }
}
```

Tag the handler as `telegram.action.handler` in your service configuration (or use autoconfiguration):

```yaml
# config/services/telegram.yaml
services:
    App\Telegram\Handler\:
        resource: '../src/Telegram/Handler/'
        tags: ['telegram.action.handler']
```

Or use the Maker:

```bash
php bin/console make:telegram:handler
```

### Filters

| Filter | Description |
|---|---|
| `TextFilter('/command')` | Matches `message.text` (case-insensitive) |
| `CallbackFilter('key', 'value')` | Matches `callback_query.data` JSON |

Multiple filters (OR logic):

```php
#[TelegramRoute([new TextFilter('/help'), new TextFilter('/info')])]
```

### Views

| View | Telegram method |
|---|---|
| `TextView($html)` | `sendMessage` |
| `ImageView($file)` | `sendPhoto` |
| `VideoView($file)` | `sendVideo` |
| `DocumentView($file)` | `sendDocument` |
| `VideoNoteView($file)` | `sendVideoNote` |
| `MediaGroupView($items)` | `sendMediaGroup` |
| `LinkView($text, $title, $url)` | `sendMessage` + inline keyboard |
| `LoginLinkView($text, $title, $url)` | `sendMessage` + login_url button |
| `CollectionView($views)` | Multiple messages |

### Inline Keyboard

```php
use ChamberOrchestra\TelegramBundle\Model\CallbackOption;
use ChamberOrchestra\TelegramBundle\Model\OptionsCollection;
use ChamberOrchestra\TelegramBundle\View\TextView;

$view = new TextView('Choose an option:');
$view->addInlineKeyboardCollection(
    (new OptionsCollection())
        ->row([
            new CallbackOption('Option A', ['path' => 'my-action', 'value' => 'a']),
            new CallbackOption('Option B', ['path' => 'my-action', 'value' => 'b']),
        ])
        ->add(new CallbackOption('Option C', ['path' => 'my-action', 'value' => 'c']))
);
```

### Events

Subscribe to bundle events for persistence or logging:

```php
use ChamberOrchestra\TelegramBundle\Event\TelegramRequestEvent;
use ChamberOrchestra\TelegramBundle\Event\TelegramMessageSentEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(TelegramRequestEvent::class, priority: 255)]
class PersistBotRequestListener
{
    public function __invoke(TelegramRequestEvent $event): void
    {
        // $event->payload — raw webhook array
        // $event->userId  — Telegram user ID string
    }
}

#[AsEventListener(TelegramMessageSentEvent::class)]
class PersistBotResponseListener
{
    public function __invoke(TelegramMessageSentEvent $event): void
    {
        // $event->response — Telegram API response array
        // $event->method   — API method name (sendMessage, etc.)
    }
}
```

## Requirements

- PHP 8.5+
- Symfony 8.0
- Symfony Messenger (with an async transport)

## License

Apache-2.0. See [LICENSE](LICENSE).
