<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\DependencyInjection\Compiler;

use ChamberOrchestra\TelegramBundle\Client\Telegram;
use ChamberOrchestra\TelegramBundle\Messenger\Handler\SendMessageHandler;
use ChamberOrchestra\TelegramBundle\Messenger\Handler\TelegramWebhookHandler;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConfigureServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $rateLimiter = $container->getParameter('chamber_orchestra_telegram.rate_limiter');
        $loggerChannel = $container->getParameter('chamber_orchestra_telegram.logger_channel');
        $bus = $container->getParameter('chamber_orchestra_telegram.bus');
        $fallbackHandler = $container->getParameter('chamber_orchestra_telegram.fallback_handler');

        if ($container->hasDefinition(SendMessageHandler::class)) {
            $container->getDefinition(SendMessageHandler::class)
                ->setArgument('$telegramLimiter', new Reference($rateLimiter))
                ->setArgument('$bus', new Reference($bus));
        }

        if ($container->hasDefinition(TelegramWebhookHandler::class)) {
            $container->getDefinition(TelegramWebhookHandler::class)
                ->setArgument('$logger', new Reference('monolog.logger.' . $loggerChannel));
        }

        if ($container->hasDefinition(Telegram::class)) {
            $container->getDefinition(Telegram::class)
                ->setArgument('$bus', new Reference($bus));
        }

        if ($container->hasDefinition(ActionHandlerResolver::class)) {
            $container->getDefinition(ActionHandlerResolver::class)
                ->setArgument('$fallbackHandlerClass', $fallbackHandler);
        }
    }
}
