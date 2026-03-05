<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Resolver;

use ChamberOrchestra\TelegramBundle\Attribute\TelegramRoute;
use ChamberOrchestra\TelegramBundle\Contracts\Handler\HandlerInterface;
use ChamberOrchestra\TelegramBundle\Handler\FallbackHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ActionHandlerResolver
{
    public function __construct(
        #[TaggedIterator('telegram.action.handler')]
        private readonly iterable $handlers,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug,
        private readonly string $fallbackHandlerClass = FallbackHandler::class,
    ) {
    }

    public function resolve(array $data): HandlerInterface
    {
        foreach ($this->handlers as $handler) {
            $attrs = (new \ReflectionClass($handler))->getAttributes(TelegramRoute::class);
            foreach ($attrs as $attr) {
                /** @var TelegramRoute $route */
                $route = $attr->newInstance();
                $filters = \is_array($route->filter) ? $route->filter : [$route->filter];
                foreach ($filters as $filter) {
                    if ($filter->matches($data)) {
                        return $handler;
                    }
                }
            }
        }

        return $this->getHandlerByClassName($this->fallbackHandlerClass);
    }

    public function getHandlerByClassName(string $class): HandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof $class) {
                return $handler;
            }
        }

        throw new \OutOfBoundsException(\sprintf('Handler "%s" not found.', $class));
    }
}
