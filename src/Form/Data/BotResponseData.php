<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class BotResponseData
{
    public string|null $messageId;
    public string|null $text;
    public string|null $caption;
    public InlineKeyboard|null $replyMarkup;

    public function __construct(array $data)
    {
        $this->messageId = isset($data['message_id']) ? (string) $data['message_id'] : null;
        $this->text = $data['text'] ?? null;
        $this->caption = $data['caption'] ?? null;
        $this->replyMarkup = isset($data['reply_markup']['inline_keyboard'])
            ? new InlineKeyboard($data['reply_markup']['inline_keyboard'])
            : null;
    }

    public function getInlineKeyboardButtons(): array
    {
        if (!$this->replyMarkup) {
            return [];
        }

        $buttons = [];
        foreach ($this->replyMarkup->keyboard as $row) {
            foreach ($row as $button) {
                $buttons[] = $button->text;
            }
        }

        return $buttons;
    }
}
