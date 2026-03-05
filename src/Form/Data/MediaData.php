<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

abstract class MediaData extends FileData
{
    public function __construct(
        string $fileId,
        string $fileUniqueId,
        int $fileSize,
        private readonly int $width,
        private readonly int $height,
    ) {
        parent::__construct($fileId, $fileUniqueId, $fileSize);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
