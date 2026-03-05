<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Filter;

use ChamberOrchestra\TelegramBundle\Filter\CallbackFilter;
use PHPUnit\Framework\TestCase;
use ChamberOrchestra\TelegramBundle\Tests\Fixtures\Payloads;

class CallbackFilterTest extends TestCase
{
    public function testMatchesCallbackData(): void
    {
        $filter = new CallbackFilter('path', 'demo-option');
        $this->assertTrue($filter->matches(Payloads::callbackQuery(['path' => 'demo-option', 'value' => 'a'])));
    }

    public function testDoesNotMatchDifferentValue(): void
    {
        $filter = new CallbackFilter('path', 'demo-option');
        $this->assertFalse($filter->matches(Payloads::callbackQuery(['path' => 'other'])));
    }

    public function testDoesNotMatchMissingKey(): void
    {
        $filter = new CallbackFilter('path', 'demo-option');
        $this->assertFalse($filter->matches(Payloads::callbackQuery(['action' => 'demo-option'])));
    }

    public function testDoesNotMatchTextMessage(): void
    {
        $filter = new CallbackFilter('path', 'demo-option');
        $this->assertFalse($filter->matches(Payloads::textMessage('demo-option')));
    }

    public function testDoesNotMatchEmptyPayload(): void
    {
        $this->assertFalse((new CallbackFilter('path', 'x'))->matches([]));
    }
}
