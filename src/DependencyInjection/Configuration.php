<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\DependencyInjection;

use ChamberOrchestra\TelegramBundle\Handler\FallbackHandler;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chamber_orchestra_telegram');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('rate_limiter')
                    ->defaultValue('limiter.telegram')
                    ->info('Service ID of the RateLimiterFactory used by SendMessageHandler.')
                ->end()
                ->scalarNode('logger_channel')
                    ->defaultValue('telegram')
                    ->info('Monolog channel name (monolog.logger.<channel>).')
                ->end()
                ->scalarNode('bus')
                    ->defaultValue('messenger.default_bus')
                    ->info('Service ID of the MessageBus used to dispatch Telegram messages.')
                ->end()
                ->scalarNode('fallback_handler')
                    ->defaultValue(FallbackHandler::class)
                    ->info('FQCN of the handler invoked when no route matches.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
