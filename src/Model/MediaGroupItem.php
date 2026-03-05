<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Model;

use ChamberOrchestra\TelegramBundle\Contracts\Model\OptionInterface;
use JsonSerializable;
use Symfony\Component\HttpFoundation\File\File;

class MediaGroupItem implements OptionInterface, JsonSerializable
{
    public File $file;
    private mixed $media;

    public function __construct(File $file, public string $type = 'photo', public string|null $caption = null)
    {
        $this->file = $file;
        $this->media = 'attach://' . $file->getBasename();
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function getMedia(): mixed
    {
        return $this->media;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCaption(): string|null
    {
        return $this->caption;
    }

    public function getName(): string
    {
        return $this->file->getBasename();
    }

    public function jsonSerialize(): mixed
    {
        return \array_filter([
            'type' => $this->getType(),
            'media' => $this->getMedia(),
            'caption' => $this->getCaption(),
        ]);
    }
}
