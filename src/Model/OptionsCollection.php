<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Model;

use ChamberOrchestra\TelegramBundle\Contracts\Model\OptionInterface;

class OptionsCollection
{
    /** @var array<OptionInterface|OptionInterface[]> */
    private array $options = [];

    public function add(OptionInterface $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    /** @param OptionInterface[] $options */
    public function row(array $options): self
    {
        $this->options[] = $options;

        return $this;
    }

    /** @return array<OptionInterface|OptionInterface[]> */
    public function getOptions(): array
    {
        return $this->options;
    }
}
