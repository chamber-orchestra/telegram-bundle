<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Contracts\Model\CallbackOptionInterface;
use ChamberOrchestra\TelegramBundle\Contracts\Model\LinkOptionInterface;
use ChamberOrchestra\TelegramBundle\Contracts\Model\OptionInterface;
use ChamberOrchestra\TelegramBundle\Contracts\View\ViewInterface;
use ChamberOrchestra\TelegramBundle\Model\OptionsCollection;
use ChamberOrchestra\TelegramBundle\Model\TextOption;

abstract class AbstractView implements ViewInterface
{
    protected string $method;
    protected array $data = [];

    public function getData(): array
    {
        return $this->data;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function addInlineKeyboardCollection(OptionsCollection $collection): static
    {
        $this->data['reply_markup'] = \json_encode([
            'inline_keyboard' => $this->buildRows($collection->getOptions()),
        ]);

        return $this;
    }

    public function addKeyboardCollection(OptionsCollection $collection, int $columns = 2): static
    {
        $this->data['reply_markup'] = \json_encode([
            'keyboard' => $this->buildRowsPerColumns($collection->getOptions(), $columns),
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);

        return $this;
    }

    public function removeKeyboard(): static
    {
        $this->data['reply_markup'] = \json_encode(['remove_keyboard' => true]);

        return $this;
    }

    /** @param array<OptionInterface|OptionInterface[]> $options */
    private function buildRows(array $options): array
    {
        $rows = [];
        foreach ($options as $option) {
            if (\is_array($option)) {
                $rows[] = \array_map($this->buildOptionRow(...), $option);
            } else {
                $rows[] = [$this->buildOptionRow($option)];
            }
        }

        return $rows;
    }

    /** @param OptionInterface[] $options */
    private function buildRowsPerColumns(array $options, int $columns): array
    {
        $rows = [];
        $current = [];

        foreach ($options as $option) {
            $current[] = $this->buildOptionRow($option);
            if (\count($current) === $columns) {
                $rows[] = $current;
                $current = [];
            }
        }

        if ([] !== $current) {
            $rows[] = $current;
        }

        return $rows;
    }

    private function buildOptionRow(OptionInterface $option): array
    {
        return match (true) {
            $option instanceof LinkOptionInterface => [
                'text' => $option->getLinkTitle(),
                'url' => $option->getLink(),
            ],
            $option instanceof CallbackOptionInterface => [
                'text' => $option->getName(),
                'callback_data' => \json_encode($option->getCallbackData()),
            ],
            $option instanceof TextOption => [
                'text' => $option->getName(),
            ],
            default => throw new \InvalidArgumentException(\sprintf('Unsupported option type: %s', $option::class)),
        };
    }
}
