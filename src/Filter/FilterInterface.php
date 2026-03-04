<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Filter;

interface FilterInterface
{
    public function matches(array $payload): bool;
}
