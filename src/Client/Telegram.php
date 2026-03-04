<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Client;

use ChamberOrchestra\TelegramBundle\Contracts\Token\TokenProviderInterface;
use ChamberOrchestra\TelegramBundle\Exception\ClientException;
use ChamberOrchestra\TelegramBundle\Messenger\Message\CreateBotResponseMessage;
use ChamberOrchestra\TelegramBundle\Messenger\Message\SendMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class Telegram
{
    public const string EDIT_MESSAGE_REPLY_MARKUP = 'editMessageReplyMarkup';

    private TelegramClient|null $client = null;

    public function __construct(
        private readonly TokenProviderInterface $tokenProvider,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Dispatch via message queue (non-blocking).
     */
    public function send(string $method, string $chatId, array $data, string $type = 'request'): void
    {
        foreach (['photo_path', 'video_path', 'thumbnail_path', 'document_path'] as $field) {
            unset($data[$field]);
        }

        $this->bus->dispatch(new SendMessage($method, $data, $chatId, $type));
    }

    /**
     * Send immediately (blocking), used for multipart (files).
     */
    public function multipart(string $method, string $chatId, array $data): array
    {
        $response = $this->client()->multipart($method, \array_merge($data, ['chat_id' => $chatId]));

        if (self::EDIT_MESSAGE_REPLY_MARKUP !== $method) {
            $this->bus->dispatch(CreateBotResponseMessage::create($response));
        }

        return $response;
    }

    /**
     * Send immediately (blocking), used by SendMessageHandler from the queue.
     */
    public function doSend(string $method, string $chatId, array $data, string $type = 'request'): array
    {
        $resources = [];
        $fields = [
            'photo_path' => 'photo',
            'video_path' => 'video',
            'thumbnail_path' => 'thumbnail',
            'document_path' => 'document',
        ];

        foreach ($fields as $key => $param) {
            if (!empty($data[$key])) {
                $data[$param] = $resources[] = \fopen($data[$key], 'r');
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
                $this->bus->dispatch(CreateBotResponseMessage::create($response));
            }
        } catch (ClientException $e) {
            $this->logger->critical(\sprintf('Telegram client error: %s', $method), [
                'data' => \array_merge($data, ['chat_id' => $chatId]),
                'error' => $e->getResponse()->getContent(false),
            ]);
        } finally {
            foreach ($resources as $resource) {
                \fclose($resource);
            }
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
