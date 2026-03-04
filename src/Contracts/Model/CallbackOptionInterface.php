<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Contracts\Model;

interface CallbackOptionInterface extends OptionInterface
{
    public function getName(): string;

    public function getCallbackData(): array;
}
