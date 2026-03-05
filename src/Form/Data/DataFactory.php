<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class DataFactory
{
    public function create(array $data): MessageData|CallbackQueryData|MyChatMemberData
    {
        if (isset($data['message']['sticker'])) {
            throw new \InvalidArgumentException('Unsupported data type: sticker');
        }

        if (isset($data['message'])) {
            return new MessageData($data);
        }

        if (isset($data['callback_query'])) {
            return new CallbackQueryData($data);
        }

        if (isset($data['my_chat_member'])) {
            return new MyChatMemberData($data);
        }

        throw new \InvalidArgumentException('Unsupported data type');
    }
}
