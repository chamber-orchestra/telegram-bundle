<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Filter;

class TextFilter implements FilterInterface
{
    public function __construct(private readonly string $text)
    {
    }

    public function matches(array $payload): bool
    {
        $text = $payload['message']['text'] ?? null;

        return $text !== null && \mb_strtolower($text) === \mb_strtolower($this->text);
    }
}
