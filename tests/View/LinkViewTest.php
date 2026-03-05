<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\View;

use ChamberOrchestra\TelegramBundle\View\LinkView;
use ChamberOrchestra\TelegramBundle\View\LoginLinkView;
use PHPUnit\Framework\TestCase;

class LinkViewTest extends TestCase
{
    public function testLinkViewMethod(): void
    {
        $this->assertSame('sendMessage', (new LinkView('text', 'title', 'https://example.com'))->getMethod());
    }

    public function testLinkViewContainsUrl(): void
    {
        $view = new LinkView('Click here', 'Open', 'https://example.com');
        $markup = \json_decode($view->getData()['reply_markup'], true);

        $this->assertSame('Open', $markup['inline_keyboard'][0][0]['text']);
        $this->assertSame('https://example.com', $markup['inline_keyboard'][0][0]['url']);
    }

    public function testLoginLinkViewContainsLoginUrl(): void
    {
        $view = new LoginLinkView('Login', 'Sign in', 'https://example.com/login');
        $markup = \json_decode($view->getData()['reply_markup'], true);

        $button = $markup['inline_keyboard'][0][0];
        $this->assertSame('Sign in', $button['text']);
        $this->assertArrayHasKey('login_url', $button);
        $this->assertSame('https://example.com/login', $button['login_url']['url']);
    }
}
