<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Helper;

final class ViewHelper
{
    public static function formatText(string $text): string
    {
        return \str_replace(
            ['<br>', '<br/>', '<br />'],
            "\n",
            \html_entity_decode(self::sanitize($text)),
        );
    }

    public static function splitTextToChunks(string $text, int $chunkSize = 4096): array
    {
        $paragraphs = \preg_split("/(\r?\n){2,}/", $text);
        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $para) {
            $next = $buffer ? $buffer."\n\n".$para : $para;
            if (\mb_strlen($next) > $chunkSize) {
                $chunks[] = \trim($buffer);
                $buffer = $para;
            } else {
                $buffer = $next;
            }
        }

        if ('' !== $buffer) {
            $chunks[] = \trim($buffer);
        }

        return $chunks;
    }

    private static function sanitize(string $text): string
    {
        return \str_replace(
            ['<p>', '</p>', '<div>', '</div>', '<!-- [if !supportLists]-->', '<span>', '</span>'],
            '',
            $text,
        );
    }
}
