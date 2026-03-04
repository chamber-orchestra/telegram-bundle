<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Helper\ViewHelper;

class TextView extends AbstractView
{
    public function __construct(string $text, string $parseMode = 'HTML')
    {
        $this->method = 'sendMessage';
        $this->data['text'] = ViewHelper::formatText($text);
        $this->data['parse_mode'] = $parseMode;
    }
}
