<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\DependencyInjection;

use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ChamberOrchestraTelegramExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('chamber_orchestra_telegram.rate_limiter', $config['rate_limiter']);
        $container->setParameter('chamber_orchestra_telegram.logger_channel', $config['logger_channel']);
        $container->setParameter('chamber_orchestra_telegram.bus', $config['bus']);
        $container->setParameter('chamber_orchestra_telegram.fallback_handler', $config['fallback_handler']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag('telegram.action.handler');
    }
}
