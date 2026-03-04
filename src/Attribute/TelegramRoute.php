<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Attribute;

use Attribute;
use ChamberOrchestra\TelegramBundle\Filter\FilterInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class TelegramRoute
{
    public function __construct(
        public FilterInterface|array $filter,
    ) {
    }
}
