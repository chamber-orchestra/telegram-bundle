<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Model;

use ChamberOrchestra\TelegramBundle\Contracts\Model\CallbackOptionInterface;

/**
 * Pass an already-translated label — translation is the handler's responsibility.
 */
readonly class CallbackOption implements CallbackOptionInterface
{
    public function __construct(
        private string $label,
        private array $data = [],
    ) {
    }

    public function getName(): string
    {
        return $this->label;
    }

    public function getCallbackData(): array
    {
        return $this->data;
    }
}
