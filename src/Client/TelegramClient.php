<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Client;

use ChamberOrchestra\TelegramBundle\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TelegramClient
{
    private const string API_URL = 'https://api.telegram.org/bot%s/';

    private HttpClientInterface $http;

    public function __construct(private readonly string $token)
    {
        $this->http = HttpClient::create([
            'base_uri' => \sprintf(self::API_URL, $this->token),
            'timeout' => 5,
        ]);
    }

    public function request(string $method, array $params = [], string $httpMethod = 'POST'): array
    {
        try {
            $response = $this->http->request($httpMethod, $method, $this->buildOptions($params, $httpMethod));

            return $this->decode($response);
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($e->getResponse());
        }
    }

    public function multipart(string $method, array $params = []): array
    {
        $formData = new FormDataPart($params);

        try {
            $response = $this->http->request('POST', $method, [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            return $this->decode($response);
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($e->getResponse());
        }
    }

    public function fileUrl(string $path): string
    {
        return \sprintf('https://api.telegram.org/file/bot%s/%s', $this->token, $path);
    }

    private function buildOptions(array $params, string $method): array
    {
        return match (\strtoupper($method)) {
            'GET' => ['query' => $params],
            default => ['body' => $params],
        };
    }

    private function decode(ResponseInterface $response): array
    {
        $content = $response->getContent();

        return '' === $content ? [] : $response->toArray();
    }
}
