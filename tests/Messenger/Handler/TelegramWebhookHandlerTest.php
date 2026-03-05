<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Messenger\Handler;

use ChamberOrchestra\TelegramBundle\Event\TelegramActionResolvedEvent;
use ChamberOrchestra\TelegramBundle\Event\TelegramRequestEvent;
use ChamberOrchestra\TelegramBundle\Form\Data\DataFactory;
use ChamberOrchestra\TelegramBundle\Form\Data\MessageData;
use ChamberOrchestra\TelegramBundle\Messenger\Handler\TelegramWebhookHandler;
use ChamberOrchestra\TelegramBundle\Messenger\Message\TelegramWebhookMessage;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ChamberOrchestra\TelegramBundle\Tests\Fixtures\Payloads;
use ChamberOrchestra\TelegramBundle\Tests\Resolver\StubStartHandler;

class TelegramWebhookHandlerTest extends TestCase
{
    private ActionHandlerResolver&MockObject $resolver;
    private DataFactory&MockObject $dataFactory;
    private EventDispatcherInterface&MockObject $dispatcher;
    private LoggerInterface&MockObject $logger;
    private TelegramWebhookHandler $handler;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(ActionHandlerResolver::class);
        $this->dataFactory = $this->createMock(DataFactory::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new TelegramWebhookHandler(
            $this->resolver,
            $this->dataFactory,
            $this->dispatcher,
            $this->logger,
        );
    }

    public function testDispatchesEventsAndCallsHandler(): void
    {
        $payload = Payloads::textMessage('/start');
        $dto = $this->createMock(MessageData::class);
        $actionHandler = $this->createMock(StubStartHandler::class);

        $this->resolver->expects($this->once())->method('resolve')->with($payload)->willReturn($actionHandler);
        $this->dataFactory->expects($this->once())->method('create')->with($payload)->willReturn($dto);
        $this->dispatcher->expects($this->exactly(2))->method('dispatch')->with(
            $this->logicalOr(
                $this->isInstanceOf(TelegramRequestEvent::class),
                $this->isInstanceOf(TelegramActionResolvedEvent::class),
            ),
        );
        $actionHandler->expects($this->once())->method('__invoke')->with($dto);

        ($this->handler)(new TelegramWebhookMessage(\json_encode($payload), '42'));
    }

    public function testSkipsEventsWhenNoUserId(): void
    {
        $payload = Payloads::textMessage('/start');
        $dto = $this->createMock(MessageData::class);
        $actionHandler = $this->createMock(StubStartHandler::class);

        $this->resolver->method('resolve')->willReturn($actionHandler);
        $this->dataFactory->method('create')->willReturn($dto);

        // No events dispatched when userId is null
        $this->dispatcher->expects($this->never())->method('dispatch');

        ($this->handler)(new TelegramWebhookMessage(\json_encode($payload), null));
    }

    public function testLogsWarningOnInvalidJson(): void
    {
        $this->logger->expects($this->once())->method('warning')->with(
            'Telegram webhook: invalid JSON payload',
            $this->anything(),
        );
        $this->resolver->expects($this->never())->method('resolve');

        ($this->handler)(new TelegramWebhookMessage('not-json', null));
    }

    public function testRethrowsHandlerExceptions(): void
    {
        $this->expectException(\RuntimeException::class);

        $payload = Payloads::textMessage('/start');
        $actionHandler = $this->createMock(StubStartHandler::class);
        $actionHandler->method('__invoke')->willThrowException(new \RuntimeException('boom'));

        $this->resolver->method('resolve')->willReturn($actionHandler);
        $this->dataFactory->method('create')->willReturn($this->createMock(MessageData::class));
        $this->logger->expects($this->once())->method('error');

        ($this->handler)(new TelegramWebhookMessage(\json_encode($payload), null));
    }
}
