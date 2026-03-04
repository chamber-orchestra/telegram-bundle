<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use ChamberOrchestra\TelegramBundle\Helper\ViewHelper;
use Symfony\Component\HttpFoundation\File\File;

class DocumentView extends AbstractFileView
{
    public function __construct(File $file, string|null $caption = null, string $parseMode = 'HTML')
    {
        parent::__construct($file);
        $this->method = 'sendDocument';
        $this->data['document_path'] = $file->getRealPath();
        if (null !== $caption) {
            $this->data['caption'] = ViewHelper::formatText($caption);
            $this->data['parse_mode'] = $parseMode;
        }
    }
}
