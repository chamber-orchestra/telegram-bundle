<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use Symfony\Component\HttpFoundation\File\File;

/**
 * File views store the path in data; Telegram::doSend() opens the resource.
 */
abstract class AbstractFileView extends AbstractView
{
    protected File $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }
}
