<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

readonly class ChatData
{
    private string $id;
    private string|null $title;

    public function __construct(array $data)
    {
        $this->id = (string) $data['id'];
        $this->title = $data['title'] ?? null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }
}
