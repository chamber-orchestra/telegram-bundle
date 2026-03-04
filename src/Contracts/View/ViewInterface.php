<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Contracts\View;

interface ViewInterface
{
    public function getData(): array;

    public function getMethod(): string;
}
