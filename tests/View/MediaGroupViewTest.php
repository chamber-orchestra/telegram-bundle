<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Tests\View;

use ChamberOrchestra\TelegramBundle\Model\MediaGroupItem;
use ChamberOrchestra\TelegramBundle\View\MediaGroupView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\Part\DataPart;

class MediaGroupViewTest extends TestCase
{
    private string $tmpFile1;
    private string $tmpFile2;

    protected function setUp(): void
    {
        $this->tmpFile1 = \tempnam(\sys_get_temp_dir(), 'tg_test_') . '.jpg';
        $this->tmpFile2 = \tempnam(\sys_get_temp_dir(), 'tg_test_') . '.jpg';
        \file_put_contents($this->tmpFile1, 'fake-image-1');
        \file_put_contents($this->tmpFile2, 'fake-image-2');
    }

    protected function tearDown(): void
    {
        @\unlink($this->tmpFile1);
        @\unlink($this->tmpFile2);
    }

    public function testMethod(): void
    {
        $view = new MediaGroupView([
            new MediaGroupItem(new File($this->tmpFile1)),
            new MediaGroupItem(new File($this->tmpFile2)),
        ]);

        $this->assertSame('sendMediaGroup', $view->getMethod());
    }

    public function testMediaJsonHasIndexedAttachReferences(): void
    {
        $view = new MediaGroupView([
            new MediaGroupItem(new File($this->tmpFile1), type: 'photo', caption: 'First'),
            new MediaGroupItem(new File($this->tmpFile2), type: 'photo'),
        ]);

        $data = $view->getData();
        $media = \json_decode($data['media'], true);

        $this->assertCount(2, $media);
        $this->assertSame('attach://media_0', $media[0]['media']);
        $this->assertSame('attach://media_1', $media[1]['media']);
        $this->assertSame('First', $media[0]['caption']);
    }

    public function testDataPartsCreatedForEachFile(): void
    {
        $view = new MediaGroupView([
            new MediaGroupItem(new File($this->tmpFile1)),
            new MediaGroupItem(new File($this->tmpFile2)),
        ]);

        $data = $view->getData();

        $this->assertInstanceOf(DataPart::class, $data['media_0']);
        $this->assertInstanceOf(DataPart::class, $data['media_1']);
    }

    public function testSameFileUsedTwiceNoKeyCollision(): void
    {
        // Two items with the same file should still produce distinct keys
        $view = new MediaGroupView([
            new MediaGroupItem(new File($this->tmpFile1)),
            new MediaGroupItem(new File($this->tmpFile1)),
        ]);

        $data = $view->getData();

        $this->assertArrayHasKey('media_0', $data);
        $this->assertArrayHasKey('media_1', $data);
    }

    public function testCaptionAppliedToFirstItem(): void
    {
        $view = new MediaGroupView(
            [
                new MediaGroupItem(new File($this->tmpFile1)),
                new MediaGroupItem(new File($this->tmpFile2)),
            ],
            caption: 'Album caption',
        );

        $media = \json_decode($view->getData()['media'], true);

        $this->assertSame('Album caption', $media[0]['caption']);
        $this->assertArrayNotHasKey('caption', $media[1]);
    }
}
