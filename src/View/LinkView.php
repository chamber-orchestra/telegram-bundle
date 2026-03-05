<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Helper\ViewHelper;

class LinkView extends AbstractView
{
    public function __construct(string $text, string $linkTitle, string $link, string $mode = 'HTML')
    {
        $this->method = 'sendMessage';
        $this->data['text'] = ViewHelper::formatText($text);
        $this->data['reply_markup'] = \json_encode([
            'inline_keyboard' => [[[
                'text' => $linkTitle,
                'url' => $link,
            ]]],
        ]);
        $this->data['parse_mode'] = $mode;
    }
}
