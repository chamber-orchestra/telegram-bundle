<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class MyChatMemberData extends AbstractData
{
    private string|null $status = null;

    public function __construct(array $data)
    {
        parent::__construct($data['my_chat_member']['from'], $data);
        $this->status = $data['my_chat_member']['new_chat_member']['status'] ?? null;
    }

    public function getStatus(): string|null
    {
        return $this->status;
    }
}
