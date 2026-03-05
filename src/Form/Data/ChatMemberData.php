<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

readonly class ChatMemberData
{
    private string $id;
    private bool|null $isBot;
    private string|null $firstname;
    private string|null $username;

    public function __construct(array $data)
    {
        $this->id = (string) $data['id'];
        $this->isBot = $data['is_bot'] ?? null;
        $this->firstname = $data['first_name'] ?? null;
        $this->username = $data['username'] ?? null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isBot(): bool|null
    {
        return $this->isBot;
    }

    public function getFirstname(): string|null
    {
        return $this->firstname;
    }

    public function getUsername(): string|null
    {
        return $this->username;
    }
}
