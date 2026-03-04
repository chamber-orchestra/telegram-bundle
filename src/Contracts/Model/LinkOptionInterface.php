<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Contracts\Model;

interface LinkOptionInterface extends OptionInterface
{
    public function getLinkTitle(): string;

    public function getLink(): string;
}
