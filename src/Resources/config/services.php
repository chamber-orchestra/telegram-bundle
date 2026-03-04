<?php

declare(strict_types=1);

use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->instanceof(HandlerInterface::class)
        ->tag('telegram.action.handler');

    $services->load('ChamberOrchestra\\TelegramBundle\\', '../../')
        ->exclude('../../{DependencyInjection,Resources,Exception,Entity,Form/Data,Contracts,Attribute,tests}');

    $services->set(ActionHandlerResolver::class)
        ->autowire()
        ->autoconfigure()
        ->arg('$handlers', tagged_iterator('telegram.action.handler'));
};
