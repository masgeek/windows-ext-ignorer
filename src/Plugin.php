<?php

declare(strict_types=1);

namespace Masgeek;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreCommandRunEvent;

final class Plugin implements PluginInterface, EventSubscriberInterface, Capable
{
    protected array $ignoredExtensions = ['ext-pcntl', 'ext-posix'];

    protected Composer $composer;

    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void {/**will not implment*/}
    public function uninstall(Composer $composer, IOInterface $io): void {/**will not implment*/}

    public function getCapabilities(): array
    {
        return [
            CommandProviderCapability::class => CommandProvider::class
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::PRE_COMMAND_RUN => ['onPreCommandRun', 1],
        ];
    }

    public function onPreCommandRun(PreCommandRunEvent $event): void
    {
        if (!$this->isWindows()) {
            return;
        }

        $commandName = $event->getCommand();

        $this->io->writeError("➡️  Running command: <comment>$commandName</comment>", true, IOInterface::VERBOSE);
        $this->io->write("<options=bold>========= Masgeek Windows Ext Ignorer =========</>", true, IOInterface::NORMAL);
        $this->io->write("<info>✅ Detected Windows OS. Ignoring extensions:</info>", true, IOInterface::NORMAL);
        $this->io->write('<comment>' . implode(', ', $this->ignoredExtensions) . '</comment>', true, IOInterface::VERBOSE);
        $this->io->debug("<options=bold>=============================================</>" . PHP_EOL);

        $input = $event->getInput();

        $existingIgnores = (array) $input->getOption('ignore-platform-req');
        $newIgnores = array_unique(array_merge($existingIgnores, $this->ignoredExtensions));

        $input->setOption('ignore-platform-req', $newIgnores);

        $this->io->write("<info>✅ Platform requirements bypassed for: <comment>$commandName</comment></info>", true, IOInterface::NORMAL);
    }

    private function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
