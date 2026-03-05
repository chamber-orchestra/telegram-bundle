<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Form\Data;

class MessageData extends AbstractData
{
    private string|null $text = null;
    /** @var PhotoData[] */
    private array $images = [];
    private VideoData|null $video = null;
    private DocumentData|null $document = null;
    private string|null $caption = null;
    /** @var ChatMemberData[] */
    private array $newChatMembers = [];
    private ChatMemberData|null $leftChatMember = null;
    private ChatData|null $chat = null;

    public function __construct(array $data)
    {
        parent::__construct($data['message']['from'], $data);
        $this->messageId = (string) $data['message']['message_id'];
        $this->text = $data['message']['text'] ?? null;

        if (isset($data['message']['photo'])) {
            $this->images = array_map(
                fn(array $p) => new PhotoData($p['file_id'], $p['file_unique_id'], $p['file_size'], $p['width'], $p['height']),
                $data['message']['photo'],
            );
        }

        if (isset($data['message']['video'])) {
            $v = $data['message']['video'];
            $this->video = new VideoData($v['file_id'], $v['file_unique_id'], $v['file_size'], $v['width'], $v['height']);
        }

        if (isset($data['message']['document'])) {
            $d = $data['message']['document'];
            $this->document = new DocumentData($d['file_id'], $d['file_unique_id'], $d['file_size'], $d['mime_type'] ?? null);
        }

        $this->caption = $data['message']['caption'] ?? null;

        foreach ($data['message']['new_chat_members'] ?? [] as $member) {
            $this->newChatMembers[] = new ChatMemberData($member);
        }

        if (isset($data['message']['left_chat_member'])) {
            $this->leftChatMember = new ChatMemberData($data['message']['left_chat_member']);
        }

        if (isset($data['message']['chat'])) {
            $this->chat = new ChatData($data['message']['chat']);
        }
    }

    public function getText(): string|null
    {
        return $this->text;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function hasImages(): bool
    {
        return count($this->images) > 0;
    }

    public function getBestQualityImage(): PhotoData|null
    {
        $best = null;
        foreach ($this->images as $image) {
            if ($best === null || $image->getFileSize() > $best->getFileSize()) {
                $best = $image;
            }
        }

        return $best;
    }

    public function getCaption(): string|null
    {
        return $this->caption;
    }

    public function getVideo(): VideoData|null
    {
        return $this->video;
    }

    public function getDocument(): DocumentData|null
    {
        return $this->document;
    }

    public function getNewChatMembers(): array
    {
        return $this->newChatMembers;
    }

    public function getLeftChatMember(): ChatMemberData|null
    {
        return $this->leftChatMember;
    }

    public function getChat(): ChatData|null
    {
        return $this->chat;
    }
}
