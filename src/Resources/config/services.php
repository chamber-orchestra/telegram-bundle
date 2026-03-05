<?php

declare(strict_types=1);

use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use ChamberOrchestra\TelegramBundle\Controller\WebhookController;
use ChamberOrchestra\TelegramBundle\Form\Data\DataFactory;
use ChamberOrchestra\TelegramBundle\Maker\MakeTelegramHandler;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
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
        ->exclude('../../{DependencyInjection,Resources,Exception,Entity,Repository,Form/Data,Contracts,Attribute,Messenger/Message,Event,Maker,tests}');

    if (\class_exists(AbstractMaker::class)) {
        $services->set(MakeTelegramHandler::class)->autowire()->autoconfigure()->tag('maker.command');
    }

    $services->set(DataFactory::class)->autowire();

    $services->set(ActionHandlerResolver::class)
        ->autowire()
        ->autoconfigure()
        ->arg('$handlers', tagged_iterator('telegram.action.handler'));

    $services->set(WebhookController::class)
        ->autowire()
        ->autoconfigure()
        ->public()
        ->tag('controller.service_arguments');
};
