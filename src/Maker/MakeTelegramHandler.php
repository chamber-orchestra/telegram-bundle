<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates a Telegram action handler class.
 *
 * Usage:
 *   php bin/console make:telegram:handler StartHandler
 *   php bin/console make:telegram:handler DemoHandler --filter=callback --key=path --value=demo
 */
final class MakeTelegramHandler extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:telegram:handler';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Telegram action handler';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->addArgument('name', InputArgument::OPTIONAL, 'Handler class name (e.g. <fg=yellow>StartHandler</>)')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter type: <fg=yellow>text</> or <fg=yellow>callback</>', 'text')
            ->addOption('command', null, InputOption::VALUE_OPTIONAL, 'Text command to match (e.g. <fg=yellow>/start</>)', '/start')
            ->addOption('key', null, InputOption::VALUE_OPTIONAL, 'Callback key (for callback filter)', 'path')
            ->addOption('value', null, InputOption::VALUE_OPTIONAL, 'Callback value (for callback filter)', 'my-action')
        ;

        $inputConf->setArgumentAsNonInteractive('name');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if (!$input->getArgument('name')) {
            $name = $io->ask('Handler class name (e.g. StartHandler)', 'StartHandler');
            $input->setArgument('name', $name);
        }

        if (!$input->getOption('filter')) {
            $filter = $io->choice('Filter type', ['text', 'callback'], 'text');
            $input->setOption('filter', $filter);
        }

        if ('text' === $input->getOption('filter') && '/start' === $input->getOption('command')) {
            $command2 = $io->ask('Text command to match', '/start');
            $input->setOption('command', $command2);
        }

        if ('callback' === $input->getOption('filter')) {
            if ('path' === $input->getOption('key')) {
                $key = $io->ask('Callback key', 'path');
                $input->setOption('key', $key);
            }
            if ('my-action' === $input->getOption('value')) {
                $value = $io->ask('Callback value', 'my-action');
                $input->setOption('value', $value);
            }
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $name = $input->getArgument('name');
        $filterType = $input->getOption('filter');

        // Ensure name ends with Handler
        if (!str_ends_with($name, 'Handler')) {
            $name .= 'Handler';
        }

        $classNameDetails = $generator->createClassNameDetails(
            $name,
            'Telegram\\Handler\\',
            'Handler',
        );

        $filterAttribute = $this->buildFilterAttribute($filterType, $input);
        $useStatements = $this->buildUseStatements($filterType);

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/TelegramHandler.tpl.php',
            [
                'namespace' => Str::getNamespace($classNameDetails->getFullName()),
                'class_name' => $classNameDetails->getShortName(),
                'filter_attribute' => $filterAttribute,
                'use_statements' => $useStatements,
            ],
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
        $io->text([
            'Next: implement <fg=yellow>__invoke(AbstractData $dto)</> in your new handler.',
            'Remember to register it in <fg=yellow>config/services/telegram.yaml</> if not auto-discovered.',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    private function buildFilterAttribute(string $filterType, InputInterface $input): string
    {
        if ('callback' === $filterType) {
            $key = $input->getOption('key');
            $value = $input->getOption('value');

            return \sprintf("#[TelegramRoute(new CallbackFilter('%s', '%s'))]", $key, $value);
        }

        $command = $input->getOption('command');

        return \sprintf("#[TelegramRoute(new TextFilter('%s'))]", $command);
    }

    private function buildUseStatements(string $filterType): string
    {
        $uses = [
            'use ChamberOrchestra\\TelegramBundle\\Attribute\\TelegramRoute;',
            'use ChamberOrchestra\\TelegramBundle\\Form\\Data\\AbstractData;',
            'use ChamberOrchestra\\TelegramBundle\\Handler\\AbstractActionHandler;',
        ];

        if ('callback' === $filterType) {
            $uses[] = 'use ChamberOrchestra\\TelegramBundle\\Filter\\CallbackFilter;';
        } else {
            $uses[] = 'use ChamberOrchestra\\TelegramBundle\\Filter\\TextFilter;';
        }

        \sort($uses);

        return \implode("\n", $uses);
    }
}
