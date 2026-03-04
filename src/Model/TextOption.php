<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Model;

use ChamberOrchestra\TelegramBundle\Contracts\Model\OptionInterface;

readonly class TextOption implements OptionInterface
{
    public function __construct(public readonly string $text)
    {
    }

    public function getName(): string
    {
        return $this->text;
    }
}
