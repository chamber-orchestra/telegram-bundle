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
    public array $rawData = [];

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }
}
