<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Handler;

use ChamberOrchestra\TelegramBundle\Client\Telegram;
use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use ChamberOrchestra\TelegramBundle\Helper\MessageRenderer;
use ChamberOrchestra\TelegramBundle\Resolver\ActionHandlerResolver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractActionHandler implements HandlerInterface
{
    protected Telegram $telegram;
    protected MessageRenderer $renderer;
    protected ActionHandlerResolver $actionHandlerResolver;
    protected MessageBusInterface $bus;
    protected EventDispatcherInterface $dispatcher;
    protected LoggerInterface $logger;
    protected EntityManagerInterface $em;
    protected ValidatorInterface $validator;
    protected bool $debug;
    protected string $publicDir;

    #[Required]
    public function setTelegram(Telegram $telegram): void
    {
        $this->telegram = $telegram;
    }

    #[Required]
    public function setRenderer(MessageRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    #[Required]
    public function setActionHandlerResolver(ActionHandlerResolver $value): void
    {
        $this->actionHandlerResolver = $value;
    }

    #[Required]
    public function setBus(MessageBusInterface $bus): void
    {
        $this->bus = $bus;
    }

    #[Required]
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    #[Required]
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    #[Required]
    public function setDebug(#[Autowire('%kernel.debug%')] bool $value): void
    {
        $this->debug = $value;
    }

    #[Required]
    public function setPublicDir(#[Autowire('%kernel.project_dir%/public')] string $value): void
    {
        $this->publicDir = $value;
    }
}
