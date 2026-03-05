<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Event;

readonly class TelegramMessageSentEvent
{
    public function __construct(
        public array $response,
        public string $method,
    ) {
    }
}
