<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class CallbackQueryData extends AbstractData
{
    private array $callbackData = [];
    public InlineKeyboard|null $replyMarkup = null;

    public function __construct(array $data)
    {
        parent::__construct($data['callback_query']['from'], $data);
        $this->messageId = (string) $data['callback_query']['message']['message_id'];

        if (isset($data['callback_query']['data'])) {
            $this->callbackData = json_decode($data['callback_query']['data'], true);
            $this->replyMarkup = isset($data['callback_query']['message']['reply_markup']['inline_keyboard'])
                ? new InlineKeyboard($data['callback_query']['message']['reply_markup']['inline_keyboard'])
                : null;
        }
    }

    public function getCallbackData(): array
    {
        return $this->callbackData;
    }

    public function getCallbackDataByKey(string $key): mixed
    {
        return $this->callbackData[$key] ?? null;
    }

    public function getPath(): string|null
    {
        return $this->callbackData['path'] ?? null;
    }

    public function forward(string $path): void
    {
        $this->callbackData['path'] = $path;
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
