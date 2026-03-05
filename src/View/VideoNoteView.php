<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\View;

use Symfony\Component\HttpFoundation\File\File;

class VideoNoteView extends AbstractFileView
{
    /**
     * @param int|null $duration Duration in seconds
     * @param int|null $length   Video width/height (circle diameter) in pixels
     */
    public function __construct(File $file, int|null $duration = null, int|null $length = null)
    {
        parent::__construct($file);
        $this->method = 'sendVideoNote';
        $this->data['video_note_path'] = $file->getRealPath();

        if (null !== $duration) {
            $this->data['duration'] = $duration;
        }

        if (null !== $length) {
            $this->data['length'] = $length;
        }
    }
}
