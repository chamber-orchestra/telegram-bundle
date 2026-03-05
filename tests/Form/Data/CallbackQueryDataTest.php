<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Form\Data;

use ChamberOrchestra\TelegramBundle\Form\Data\CallbackQueryData;
use PHPUnit\Framework\TestCase;
use ChamberOrchestra\TelegramBundle\Tests\Fixtures\Payloads;

class CallbackQueryDataTest extends TestCase
{
    public function testGetCallbackData(): void
    {
        $dto = new CallbackQueryData(Payloads::callbackQuery(['path' => 'test', 'value' => '42']));

        $this->assertSame(['path' => 'test', 'value' => '42'], $dto->getCallbackData());
        $this->assertSame('test', $dto->getCallbackData()['path']);
    }

    public function testGetPath(): void
    {
        $dto = new CallbackQueryData(Payloads::callbackQuery(['path' => 'my-route']));

        $this->assertSame('my-route', $dto->getPath());
    }

    public function testGetCallbackDataByKey(): void
    {
        $dto = new CallbackQueryData(Payloads::callbackQuery(['path' => 'x', 'id' => '99']));

        $this->assertSame('99', $dto->getCallbackDataByKey('id'));
        $this->assertNull($dto->getCallbackDataByKey('missing'));
    }

    public function testForwardChangesPath(): void
    {
        $dto = new CallbackQueryData(Payloads::callbackQuery(['path' => 'old']));
        $dto->forward('new-path');

        $this->assertSame('new-path', $dto->getPath());
    }

    public function testReplyMarkupParsed(): void
    {
        $dto = new CallbackQueryData(Payloads::callbackQuery(['path' => 'x']));

        // Fixture has inline_keyboard in reply_markup
        $this->assertNotEmpty($dto->getInlineKeyboardButtons());
        $this->assertSame('Option A', $dto->getInlineKeyboardButtons()[0]);
    }

    public function testEmptyCallbackDataWhenNoData(): void
    {
        $payload = Payloads::callbackQuery(['path' => 'x']);
        unset($payload['callback_query']['data']);

        $dto = new CallbackQueryData($payload);

        $this->assertSame([], $dto->getCallbackData());
        $this->assertNull($dto->getPath());
    }
}
