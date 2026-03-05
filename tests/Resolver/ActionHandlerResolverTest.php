<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Resolver;

use ChamberOrchestra\TelegramBundle\Attribute\TelegramRoute;
use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use ChamberOrchestra\TelegramBundle\Filter\CallbackFilter;
use ChamberOrchestra\TelegramBundle\Filter\TextFilter;
use ChamberOrchestra\TelegramBundle\Form\Data\AbstractData;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use PHPUnit\Framework\TestCase;
use ChamberOrchestra\TelegramBundle\Tests\Fixtures\Payloads;

// Stub handlers with TelegramRoute attributes
#[TelegramRoute(new TextFilter('/start'))]
class StubStartHandler implements HandlerInterface
{
    public function __invoke(AbstractData $dto): void {}
}

#[TelegramRoute(new CallbackFilter('path', 'my-action'))]
class StubCallbackHandler implements HandlerInterface
{
    public function __invoke(AbstractData $dto): void {}
}

#[TelegramRoute([new TextFilter('/multi'), new TextFilter('/alias')])]
class StubMultiRouteHandler implements HandlerInterface
{
    public function __invoke(AbstractData $dto): void {}
}

class StubFallbackHandler implements HandlerInterface
{
    public function __invoke(AbstractData $dto): void {}
}

class ActionHandlerResolverTest extends TestCase
{
    private function makeResolver(array $handlers, string $fallback = StubFallbackHandler::class): ActionHandlerResolver
    {
        return new ActionHandlerResolver($handlers, debug: false, fallbackHandlerClass: $fallback);
    }

    public function testResolvesTextHandler(): void
    {
        $resolver = $this->makeResolver([new StubStartHandler(), new StubFallbackHandler()]);

        $this->assertInstanceOf(StubStartHandler::class, $resolver->resolve(Payloads::textMessage('/start')));
    }

    public function testResolvesCallbackHandler(): void
    {
        $resolver = $this->makeResolver([new StubCallbackHandler(), new StubFallbackHandler()]);

        $this->assertInstanceOf(
            StubCallbackHandler::class,
            $resolver->resolve(Payloads::callbackQuery(['path' => 'my-action'])),
        );
    }

    public function testFallsBackToFallbackHandler(): void
    {
        $resolver = $this->makeResolver([new StubStartHandler(), new StubFallbackHandler()]);

        $this->assertInstanceOf(StubFallbackHandler::class, $resolver->resolve(Payloads::textMessage('/unknown')));
    }

    public function testResolvesFirstMatchingHandler(): void
    {
        $start = new StubStartHandler();
        $fallback = new StubFallbackHandler();
        $resolver = $this->makeResolver([$start, $fallback]);

        $this->assertSame($start, $resolver->resolve(Payloads::textMessage('/start')));
    }

    public function testResolvesMultipleFilterRoutes(): void
    {
        $handler = new StubMultiRouteHandler();
        $resolver = $this->makeResolver([$handler, new StubFallbackHandler()]);

        $this->assertInstanceOf(StubMultiRouteHandler::class, $resolver->resolve(Payloads::textMessage('/multi')));
        $this->assertInstanceOf(StubMultiRouteHandler::class, $resolver->resolve(Payloads::textMessage('/alias')));
    }

    public function testGetHandlerByClassName(): void
    {
        $resolver = $this->makeResolver([new StubStartHandler(), new StubFallbackHandler()]);

        $this->assertInstanceOf(StubStartHandler::class, $resolver->getHandlerByClassName(StubStartHandler::class));
    }

    public function testGetHandlerByClassNameThrowsWhenNotFound(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $resolver = $this->makeResolver([new StubFallbackHandler()]);
        $resolver->getHandlerByClassName(StubStartHandler::class);
    }
}
