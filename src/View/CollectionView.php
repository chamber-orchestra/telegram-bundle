<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Contracts\View\ViewInterface;
use ChamberOrchestra\TelegramBundle\Model\OptionsCollection;

/**
 * A composite view that groups multiple views for sequential rendering.
 * Iterate over getViews() and call MessageRenderer::render() for each.
 */
class CollectionView extends AbstractView
{
    /** @var ViewInterface[] */
    protected array $views = [];

    public function addView(ViewInterface $view): void
    {
        $this->views[] = $view;
    }

    /** @return ViewInterface[] */
    public function getViews(): array
    {
        return $this->views;
    }

    public function addInlineKeyboardCollection(OptionsCollection $collection): static
    {
        $this->getLast()->addInlineKeyboardCollection($collection);

        return $this;
    }

    public function addKeyboardCollection(OptionsCollection $collection, int $columns = 2): static
    {
        $this->getLast()->addKeyboardCollection($collection, $columns);

        return $this;
    }

    protected function getLast(): ViewInterface
    {
        return $this->views[\array_key_last($this->views)];
    }

    public function getMethod(): string
    {
        return '';
    }

    public function getData(): array
    {
        return [];
    }
}
