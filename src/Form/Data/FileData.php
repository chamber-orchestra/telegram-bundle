<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

abstract class FileData
{
    public function __construct(
        private readonly string $fileId,
        private readonly string $fileUniqueId,
        private readonly int $fileSize,
        private readonly string|null $fileMimeType = null,
    ) {
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getFileUniqueId(): string
    {
        return $this->fileUniqueId;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getFileMimeType(): string|null
    {
        return $this->fileMimeType;
    }
}
