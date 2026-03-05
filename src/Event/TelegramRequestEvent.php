<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TelegramRequestEvent extends Event
{
    public function __construct(
        public readonly array $payload,
        public readonly string|null $userId,
    ) {
    }
}
