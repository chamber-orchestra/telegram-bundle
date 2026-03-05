# chamber-orchestra/telegram-bundle — Agent Guidelines

This is a **standalone Symfony 8 bundle** (`packages/telegram-bundle/`). When working here, treat it as an isolated library with its own tests, composer.json, and coding standards.

## Project Structure

```
packages/telegram-bundle/
  src/          ← bundle source (namespace ChamberOrchestra\TelegramBundle\)
  tests/        ← PHPUnit unit tests (namespace ChamberOrchestra\TelegramBundle\Tests\)
  composer.json
  phpunit.xml.dist
  phpstan.neon
  .php-cs-fixer.dist.php
```

## Commands

```bash
# From packages/telegram-bundle/ directory:
vendor/bin/phpunit                    # run tests
vendor/bin/php-cs-fixer fix           # fix style
vendor/bin/phpstan analyse            # static analysis

# Or from project root:
php bin/phpunit packages/telegram-bundle/tests/
```

## Coding Rules

- PHP 8.5, `declare(strict_types=1)` in every file
- PSR-12 + `@Symfony` cs-fixer preset
- No persistence in the bundle — dispatch `TelegramMessageSentEvent` / `TelegramRequestEvent` for the host project to handle
- No direct `EntityManagerInterface` usage in bundle services (only in `AbstractActionHandler` via optional setter)
- Views must never make HTTP calls — only build data arrays; `MessageRenderer` calls `Telegram::send()`
- `MediaGroupView` uses `DataPart` objects (sync path); all other file views use `*_path` string fields (async queue)
- Filters must be pure functions — no DI, no side effects

## Adding New Features

### New View type
1. Extend `AbstractView` or `AbstractFileView`
2. Set `$this->method` and `$this->data` in constructor
3. Register nothing — auto-discovered via services.php load

### New Filter
1. Implement `FilterInterface::matches(array $payload): bool`
2. Use as `#[TelegramRoute(new MyFilter(...))]` attribute argument

### New Event
1. Add to `src/Event/`
2. Dispatch from `TelegramWebhookHandler` or `Telegram` client
3. Document in `CLAUDE.md` events table

## Testing Conventions

- Tests live in `tests/`, namespace `ChamberOrchestra\TelegramBundle\Tests\`
- Use `tests/Fixtures/Payloads.php` static factory for payload arrays
- Mock dependencies with `$this->createMock()`; no Symfony kernel in unit tests
- Stub handlers in `tests/Resolver/ActionHandlerResolverTest.php` serve as reusable fixtures
