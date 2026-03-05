<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Filter;

use ChamberOrchestra\TelegramBundle\Filter\TextFilter;
use PHPUnit\Framework\TestCase;
use ChamberOrchestra\TelegramBundle\Tests\Fixtures\Payloads;

class TextFilterTest extends TestCase
{
    public function testMatchesExactText(): void
    {
        $this->assertTrue((new TextFilter('/start'))->matches(Payloads::textMessage('/start')));
    }

    public function testMatchesCaseInsensitive(): void
    {
        $this->assertTrue((new TextFilter('/START'))->matches(Payloads::textMessage('/start')));
        $this->assertTrue((new TextFilter('/start'))->matches(Payloads::textMessage('/START')));
    }

    public function testDoesNotMatchDifferentText(): void
    {
        $this->assertFalse((new TextFilter('/start'))->matches(Payloads::textMessage('/help')));
    }

    public function testDoesNotMatchPartialText(): void
    {
        $this->assertFalse((new TextFilter('start'))->matches(Payloads::textMessage('/start')));
    }

    public function testDoesNotMatchCallbackQuery(): void
    {
        $this->assertFalse((new TextFilter('/start'))->matches(Payloads::callbackQuery(['path' => 'start'])));
    }

    public function testDoesNotMatchMissingMessage(): void
    {
        $this->assertFalse((new TextFilter('/start'))->matches([]));
    }
}
