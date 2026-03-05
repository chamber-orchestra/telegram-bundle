<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Messenger\Handler;

use ChamberOrchestra\TelegramBundle\Event\TelegramActionResolvedEvent;
use ChamberOrchestra\TelegramBundle\Event\TelegramRequestEvent;
use ChamberOrchestra\TelegramBundle\Form\Data\DataFactory;
use ChamberOrchestra\TelegramBundle\Messenger\Message\TelegramWebhookMessage;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TelegramWebhookHandler
{
    public function __construct(
        private readonly ActionHandlerResolver $resolver,
        private readonly DataFactory $dataFactory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(TelegramWebhookMessage $message): void
    {
        $this->logger->debug('Telegram webhook', ['request' => $message->requestContent]);

        $payload = json_decode($message->requestContent, true);

        if (!\is_array($payload)) {
            $this->logger->warning('Telegram webhook: invalid JSON payload', [
                'request' => $message->requestContent,
            ]);

            return;
        }

        try {
            if ($message->userId !== null) {
                $this->dispatcher->dispatch(new TelegramRequestEvent($payload, $message->userId));
            }

            $handler = $this->resolver->resolve($payload);

            if ($message->userId !== null) {
                $this->dispatcher->dispatch(new TelegramActionResolvedEvent($payload, $handler::class, $message->userId));
            }

            $dto = $this->dataFactory->create($payload);
            $handler($dto);
        } catch (\Throwable $e) {
            $this->logger->error('Telegram webhook error', [
                'request' => $message->requestContent,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
