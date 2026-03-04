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
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag('telegram.action.handler');
    }
}
