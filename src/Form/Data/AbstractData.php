<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

abstract class AbstractData
{
    public string|null $id = null;
    public string|null $messageId = null;
    public string|null $nickname = null;
    public string|null $firstName = null;
    public string|null $lastName = null;
    public string|null $phone = null;
    public bool $forwarded = false;
    public array $rawData = [];

    public function __construct(array $from, array $rawData)
    {
        $this->rawData = $rawData;
        $this->id = isset($from['id']) ? (string) $from['id'] : null;
        $this->nickname = $from['username'] ?? null;
        $this->firstName = $from['first_name'] ?? null;
        $this->lastName = $from['last_name'] ?? null;
        $this->phone = $from['phone'] ?? null;
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getNickname(): string|null
    {
        return $this->nickname;
    }

    public function getFirstName(): string|null
    {
        return $this->firstName;
    }

    public function getLastName(): string|null
    {
        return $this->lastName;
    }

    public function getPhone(): string|null
    {
        return $this->phone;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }
}
