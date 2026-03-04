<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Handler;

use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use ChamberOrchestra\TelegramBundle\Form\Data\AbstractData;

class FallbackHandler implements HandlerInterface
{
    public function __invoke(AbstractData $dto): void
    {
        // Override in your application to handle unmatched messages
    }
}
