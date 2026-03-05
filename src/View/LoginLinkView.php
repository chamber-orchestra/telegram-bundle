<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Helper\ViewHelper;

class LoginLinkView extends AbstractView
{
    public function __construct(string $text, string $linkTitle, string $link, bool $requestWithAccess = true, string $mode = 'HTML')
    {
        $this->method = 'sendMessage';
        $this->data['text'] = ViewHelper::formatText($text);
        $this->data['reply_markup'] = \json_encode([
            'inline_keyboard' => [[[
                'text' => $linkTitle,
                'login_url' => [
                    'url' => $link,
                    'request_write_access' => $requestWithAccess,
                ],
            ]]],
        ]);
        $this->data['parse_mode'] = $mode;
    }
}
