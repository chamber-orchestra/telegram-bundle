<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Messenger\Message;

class TelegramWebhookMessage
{
    public function __construct(
        public readonly string $requestContent,
        public readonly string|null $userId,
    ) {
    }
}
