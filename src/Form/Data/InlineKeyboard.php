<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class InlineKeyboard
{
    /** @var InlineKeyboardButton[][] */
    public array $keyboard;

    public function __construct(array $data)
    {
        $this->keyboard = array_map(
            fn(array $row) => array_map(
                fn(array $button) => new InlineKeyboardButton($button),
                $row,
            ),
            $data,
        );
    }
}
