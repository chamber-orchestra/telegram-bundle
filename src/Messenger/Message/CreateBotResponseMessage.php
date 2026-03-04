<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Messenger\Message;

readonly class CreateBotResponseMessage
{
    public function __construct(private string $response)
    {
    }

    public static function create(array $response): self
    {
        return new self(\json_encode($response));
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
