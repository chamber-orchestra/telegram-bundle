<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Controller;

use ChamberOrchestra\TelegramBundle\Messenger\Message\TelegramWebhookMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class WebhookController extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->bus->dispatch(new TelegramWebhookMessage(
            $request->getContent(),
            $this->getUserId(),
        ));

        return new JsonResponse(['ok' => true]);
    }

    /**
     * Override in your project controller to return the authenticated user's ID.
     */
    protected function getUserId(): string|null
    {
        return null;
    }
}
