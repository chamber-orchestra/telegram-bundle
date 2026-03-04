<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Helper;

use ChamberOrchestra\TelegramBundle\Client\Telegram;
use ChamberOrchestra\TelegramBundle\Contracts\View\ViewInterface;

class MessageRenderer
{
    public function __construct(private readonly Telegram $telegram)
    {
    }

    public function render(ViewInterface $view, string $chatId): void
    {
        $this->telegram->send($view->getMethod(), $chatId, $view->getData());
    }

    public function renderSync(ViewInterface $view, string $chatId): array
    {
        return $this->telegram->doSend($view->getMethod(), $chatId, $view->getData());
    }
}
