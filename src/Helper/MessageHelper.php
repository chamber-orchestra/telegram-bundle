<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Helper;

class MessageHelper
{
    public const LINK_KEY = 'url';

    public static function isContainLink(array $items): bool
    {
        return self::array_key_exists_recursive(self::LINK_KEY, $items);
    }

    public static function array_key_exists_recursive($key, $array): bool
    {
        if (\array_key_exists($key, $array)) {
            return true;
        }
        foreach ($array as $element) {
            if (\is_array($element) && self::array_key_exists_recursive($key, $element)) {
                return true;
            }
        }

        return false;
    }
}
