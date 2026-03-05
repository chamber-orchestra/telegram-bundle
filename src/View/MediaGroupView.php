<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Model\MediaGroupItem;
use Symfony\Component\Mime\Part\DataPart;

class MediaGroupView extends AbstractView
{
    /** @param MediaGroupItem[] $media */
    public function __construct(array $media, string|null $caption = null)
    {
        $this->method = 'sendMediaGroup';

        $mediaJson = [];
        foreach ($media as $index => $mediaItem) {
            $key = 'media_' . $index;
            $item = $mediaItem->jsonSerialize();
            $item['media'] = 'attach://' . $key;
            $mediaJson[] = $item;
            $this->data[$key] = DataPart::fromPath($mediaItem->getFile()->getRealPath());
        }

        if (null !== $caption) {
            $mediaJson[0]['caption'] = $caption;
        }

        $this->data['media'] = \json_encode($mediaJson);
    }
}
