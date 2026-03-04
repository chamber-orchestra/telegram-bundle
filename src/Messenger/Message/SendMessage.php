<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Messenger\Message;

class SendMessage
{
    public function __construct(
        public readonly string $method,
        public readonly array $data,
        public readonly string $chatId,
        public readonly string $type,
    ) {
    }
}
