<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Contracts\Handler;

use ChamberOrchestra\TelegramBundle\Form\Data\AbstractData;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('telegram.action.handler')]
interface HandlerInterface
{
    public function __invoke(AbstractData $dto): void;
}
