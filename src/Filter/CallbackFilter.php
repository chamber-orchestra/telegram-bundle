<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Filter;

class CallbackFilter implements FilterInterface
{
    public function __construct(private readonly string $key, private readonly string $value)
    {
    }

    public function matches(array $payload): bool
    {
        if (!isset($payload['callback_query'])) {
            return false;
        }

        $data = \json_decode($payload['callback_query']['data'] ?? '{}', true);

        return ($data[$this->key] ?? null) === $this->value;
    }
}
