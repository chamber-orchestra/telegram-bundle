<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\Fixtures;

/**
 * Telegram webhook payload fixtures for unit tests.
 * Based on real payloads captured during live bot sessions.
 */
final class Payloads
{
    public static function textMessage(string $text = '/start', string $chatId = '678295990'): array
    {
        return [
            'update_id' => 90806825,
            'message' => [
                'message_id' => 15,
                'from' => [
                    'id' => (int) $chatId,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'username' => 'testuser',
                    'language_code' => 'en',
                ],
                'chat' => [
                    'id' => (int) $chatId,
                    'first_name' => 'Test',
                    'username' => 'testuser',
                    'type' => 'private',
                ],
                'date' => 1772696067,
                'text' => $text,
            ],
        ];
    }

    public static function callbackQuery(array $data, string $chatId = '678295990'): array
    {
        return [
            'update_id' => 90806830,
            'callback_query' => [
                'id' => '2913259095548836672',
                'from' => [
                    'id' => (int) $chatId,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'username' => 'testuser',
                    'language_code' => 'en',
                ],
                'message' => [
                    'message_id' => 20,
                    'from' => ['id' => 7777777777, 'is_bot' => true, 'first_name' => 'Bot', 'username' => 'testbot'],
                    'chat' => [
                        'id' => (int) $chatId,
                        'first_name' => 'Test',
                        'username' => 'testuser',
                        'type' => 'private',
                    ],
                    'date' => 1772696300,
                    'text' => 'Choose an option:',
                    'reply_markup' => [
                        'inline_keyboard' => [[
                            ['text' => 'Option A', 'callback_data' => \json_encode($data)],
                        ]],
                    ],
                ],
                'chat_instance' => '-1234567890123456789',
                'data' => \json_encode($data),
            ],
        ];
    }

    public static function myChatMember(string $status = 'kicked', string $chatId = '678295990'): array
    {
        return [
            'update_id' => 90806840,
            'my_chat_member' => [
                'chat' => ['id' => (int) $chatId, 'first_name' => 'Test', 'type' => 'private'],
                'from' => ['id' => (int) $chatId, 'is_bot' => false, 'first_name' => 'Test'],
                'date' => 1772696400,
                'old_chat_member' => ['user' => ['id' => 7777777777, 'is_bot' => true], 'status' => 'member'],
                'new_chat_member' => ['user' => ['id' => 7777777777, 'is_bot' => true], 'status' => $status],
            ],
        ];
    }
}
