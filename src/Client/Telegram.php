<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Client;

use ChamberOrchestra\TelegramBundle\Contracts\Token\TokenProviderInterface;
use ChamberOrchestra\TelegramBundle\Event\TelegramMessageSentEvent;
use ChamberOrchestra\TelegramBundle\Exception\ClientException;
use ChamberOrchestra\TelegramBundle\Messenger\Message\SendMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Part\DataPart;

class Telegram
{
    public const string EDIT_MESSAGE_REPLY_MARKUP = 'editMessageReplyMarkup';

    private TelegramClient|null $client = null;

    public function __construct(
        private readonly TokenProviderInterface $tokenProvider,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    private const array FILE_FIELDS = ['photo_path', 'video_path', 'video_note_path', 'thumbnail_path', 'document_path'];

    /**
     * Dispatch via message queue (non-blocking).
     * File paths are serialized as strings and opened by doSend() in the worker.
     * DataPart objects (e.g. MediaGroupView) cannot be serialized — sent synchronously.
     */
    public function send(string $method, string $chatId, array $data, string $type = 'request'): void
    {
        foreach ($data as $value) {
            if ($value instanceof DataPart) {
                $this->multipart($method, $chatId, $data);
                return;
            }
        }

        $hasFile = \array_any(self::FILE_FIELDS, fn(string $f) => !empty($data[$f]));

        $this->bus->dispatch(new SendMessage($method, $data, $chatId, $hasFile ? 'multipart' : $type));
    }

    /**
     * Send immediately (blocking) as multipart/form-data.
     * Used directly for MediaGroupView and by doSend() for file views.
     */
    public function multipart(string $method, string $chatId, array $data): array
    {
        $response = $this->client()->multipart($method, \array_merge($data, ['chat_id' => $chatId]));

        if (self::EDIT_MESSAGE_REPLY_MARKUP !== $method) {
            $this->dispatcher->dispatch(new TelegramMessageSentEvent($response, $method));
        }

        return $response;
    }

    /**
     * Send immediately (blocking), used by SendMessageHandler from the queue.
     */
    public function doSend(string $method, string $chatId, array $data, string $type = 'request'): array
    {
        $fields = [
            'photo_path'      => 'photo',
            'video_path'      => 'video',
            'video_note_path' => 'video_note',
            'thumbnail_path'  => 'thumbnail',
            'document_path'   => 'document',
        ];

        foreach ($fields as $key => $param) {
            if (!empty($data[$key])) {
                $data[$param] = DataPart::fromPath($data[$key]);
                unset($data[$key]);
            }
        }

        $response = [];

        try {
            $response = match ($type) {
                'multipart' => $this->client()->multipart($method, \array_merge($data, ['chat_id' => $chatId])),
                default => $this->client()->request($method, \array_merge($data, ['chat_id' => $chatId])),
            };

            if (self::EDIT_MESSAGE_REPLY_MARKUP !== $method) {
                $this->dispatcher->dispatch(new TelegramMessageSentEvent($response, $method));
            }
        } catch (ClientException $e) {
            $this->logger->critical(\sprintf('Telegram client error: %s', $method), [
                'data' => \array_merge($data, ['chat_id' => $chatId]),
                'error' => $e->getResponse()->getContent(false),
            ]);
        }

        return $response;
    }

    public function getToken(): string
    {
        return $this->tokenProvider->getToken();
    }

    public function getFilePath(string $fileId): string|null
    {
        try {
            $response = $this->client()->request(\sprintf('getFile?file_id=%s', $fileId), [], 'GET');
        } catch (\Exception) {
            return null;
        }

        return $response['result']['file_path'] ?? null;
    }

    public function getFileUrl(string $path): string
    {
        return $this->client()->fileUrl($path);
    }

    private function client(): TelegramClient
    {
        return $this->client ??= new TelegramClient($this->tokenProvider->getToken());
    }
}
