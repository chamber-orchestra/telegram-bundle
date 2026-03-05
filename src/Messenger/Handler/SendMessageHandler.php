<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Messenger\Handler;

use ChamberOrchestra\TelegramBundle\Client\Telegram;
use ChamberOrchestra\TelegramBundle\Messenger\Message\SendMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsMessageHandler]
class SendMessageHandler
{
    public const int DELAY = 1 * 1000;

    public function __construct(
        private readonly Telegram $telegram,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly RateLimiterFactory $telegramLimiter,
    ) {
    }

    public function __invoke(SendMessage $message): void
    {
        if (false === $this->telegramLimiter->create($message->chatId)->consume()->isAccepted()) {
            $this->bus->dispatch($message, [new DelayStamp(self::DELAY)]);

            return;
        }

        try {
            $this->telegram->doSend($message->method, $message->chatId, $message->data, $message->type);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), [
                'message' => $message->method,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
