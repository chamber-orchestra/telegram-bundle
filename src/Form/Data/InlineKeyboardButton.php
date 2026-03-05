<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class InlineKeyboardButton
{
    public string|null $text;
    public array $callbackData = [];

    public function __construct(array $data)
    {
        $this->text = $data['text'] ?? null;
        $this->callbackData = isset($data['callback_data']) ? json_decode($data['callback_data'], true) : [];
    }
}
