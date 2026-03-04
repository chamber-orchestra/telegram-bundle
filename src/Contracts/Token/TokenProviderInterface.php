<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Contracts\Token;

interface TokenProviderInterface
{
    public function getToken(): string;
}
