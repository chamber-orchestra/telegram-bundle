<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Form\Data;

use ChamberOrchestra\TelegramBundle\Form\Data\CallbackQueryData;
use ChamberOrchestra\TelegramBundle\Form\Data\DataFactory;
use ChamberOrchestra\TelegramBundle\Form\Data\MessageData;
use ChamberOrchestra\TelegramBundle\Form\Data\MyChatMemberData;
use PHPUnit\Framework\TestCase;
use ChamberOrchestra\TelegramBundle\Tests\Fixtures\Payloads;

class DataFactoryTest extends TestCase
{
    private DataFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DataFactory();
    }

    public function testCreatesMessageData(): void
    {
        $dto = $this->factory->create(Payloads::textMessage('/start'));

        $this->assertInstanceOf(MessageData::class, $dto);
        $this->assertSame('678295990', $dto->getId());
        $this->assertSame('/start', $dto->getText());
    }

    public function testCreatesCallbackQueryData(): void
    {
        $dto = $this->factory->create(Payloads::callbackQuery(['path' => 'demo-option', 'value' => 'a']));

        $this->assertInstanceOf(CallbackQueryData::class, $dto);
        $this->assertSame('678295990', $dto->getId());
        $this->assertSame('demo-option', $dto->getCallbackData()['path']);
        $this->assertSame('a', $dto->getCallbackData()['value']);
    }

    public function testCreatesMyChatMemberData(): void
    {
        $dto = $this->factory->create(Payloads::myChatMember('kicked'));

        $this->assertInstanceOf(MyChatMemberData::class, $dto);
        $this->assertSame('678295990', $dto->getId());
    }

    public function testThrowsOnStickerMessage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported data type: sticker');

        $payload = Payloads::textMessage('/start');
        $payload['message']['sticker'] = ['file_id' => 'abc'];

        $this->factory->create($payload);
    }

    public function testThrowsOnUnknownPayload(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported data type');

        $this->factory->create(['unknown_key' => []]);
    }
}
