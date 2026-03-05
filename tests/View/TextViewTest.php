<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\View;

use ChamberOrchestra\TelegramBundle\Model\CallbackOption;
use ChamberOrchestra\TelegramBundle\Model\LinkOption;
use ChamberOrchestra\TelegramBundle\Model\OptionsCollection;
use ChamberOrchestra\TelegramBundle\Model\TextOption;
use ChamberOrchestra\TelegramBundle\View\TextView;
use PHPUnit\Framework\TestCase;

class TextViewTest extends TestCase
{
    public function testMethod(): void
    {
        $this->assertSame('sendMessage', (new TextView('Hello'))->getMethod());
    }

    public function testTextIsFormatted(): void
    {
        $data = (new TextView('Hello'))->getData();

        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('parse_mode', $data);
    }

    public function testAddInlineKeyboardWithCallbackButtons(): void
    {
        $view = (new TextView('Pick'))
            ->addInlineKeyboardCollection(
                (new OptionsCollection())
                    ->row([
                        new CallbackOption('A', ['path' => 'opt', 'value' => 'a']),
                        new CallbackOption('B', ['path' => 'opt', 'value' => 'b']),
                    ])
                    ->add(new CallbackOption('C', ['path' => 'opt', 'value' => 'c'])),
            );

        $markup = \json_decode($view->getData()['reply_markup'], true);

        // Row 1: two buttons
        $this->assertCount(2, $markup['inline_keyboard'][0]);
        $this->assertSame('A', $markup['inline_keyboard'][0][0]['text']);
        $this->assertSame('B', $markup['inline_keyboard'][0][1]['text']);

        // Row 2: one button
        $this->assertCount(1, $markup['inline_keyboard'][1]);
        $this->assertSame('C', $markup['inline_keyboard'][1][0]['text']);
    }

    public function testAddInlineKeyboardWithLinkButton(): void
    {
        $view = (new TextView('Click'))
            ->addInlineKeyboardCollection(
                (new OptionsCollection())->add(new LinkOption('Google', 'https://google.com')),
            );

        $markup = \json_decode($view->getData()['reply_markup'], true);

        $this->assertSame('Google', $markup['inline_keyboard'][0][0]['text']);
        $this->assertSame('https://google.com', $markup['inline_keyboard'][0][0]['url']);
    }

    public function testAddReplyKeyboardWithColumns(): void
    {
        $view = (new TextView('Pick'))
            ->addKeyboardCollection(
                (new OptionsCollection())
                    ->add(new TextOption('One'))
                    ->add(new TextOption('Two'))
                    ->add(new TextOption('Three'))
                    ->add(new TextOption('Four')),
                columns: 2,
            );

        $markup = \json_decode($view->getData()['reply_markup'], true);

        $this->assertCount(2, $markup['keyboard']);          // 2 rows
        $this->assertCount(2, $markup['keyboard'][0]);       // 2 per row
        $this->assertTrue($markup['resize_keyboard']);
    }

    public function testRemoveKeyboard(): void
    {
        $view = (new TextView('Bye'))->removeKeyboard();
        $markup = \json_decode($view->getData()['reply_markup'], true);

        $this->assertTrue($markup['remove_keyboard']);
    }
}
