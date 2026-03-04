<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Model;

use ChamberOrchestra\TelegramBundle\Contracts\Model\LinkOptionInterface;

readonly class LinkOption implements LinkOptionInterface
{
    public function __construct(
        private string $linkTitle,
        private string $link,
    ) {
    }

    public function getLinkTitle(): string
    {
        return $this->linkTitle;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
