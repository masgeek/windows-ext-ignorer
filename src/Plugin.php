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
    /**
     * Extensions to ignore on Windows platforms.
     *
     * @var string[]
     */
    protected array $ignoredExtensions = ['ext-pcntl', 'ext-posix'];

    protected Composer $composer;

    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // No implementation needed
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // No implementation needed
    }

    /**
     * Capabilities provided by this plugin.
     *
     * @return array<string, string>
     */
    public function getCapabilities(): array
    {
        return [
            CommandProviderCapability::class => CommandProvider::class
        ];
    }

    /**
     * Subscribed events.
     *
     * @return array<string, array{0: string, 1?: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::PRE_COMMAND_RUN => ['onPreCommandRun', 1],
        ];
    }

    /**
     * Handles the pre-command-run event.
     */
    public function onPreCommandRun(PreCommandRunEvent $event): void
    {
        if (!$this->isWindows()) {
            return;
        }

        $commandName = $event->getCommand();

        $this->io->writeError("➡️  Running command: <comment>$commandName</comment>", true, IOInterface::VERBOSE);
        $this->io->write("<options=bold>========= IgnorePcntlPlugin =========</>", true, IOInterface::NORMAL);
        $this->io->write("<info>✅ Detected Windows OS. Automatically ignoring platform requirements:</info>", true, IOInterface::NORMAL);
        $this->io->write('<comment>' . implode(', ', $this->ignoredExtensions) . '</comment>', true, IOInterface::VERBOSE);
        $this->io->debug("<options=bold>====================================</>" . PHP_EOL);

        $input = $event->getInput();

        $existingIgnores = (array)$input->getOption('ignore-platform-req');
        $newIgnores = array_unique(array_merge($existingIgnores, $this->ignoredExtensions));

        $input->setOption('ignore-platform-req', $newIgnores);

        $this->io->write("<info>✅ Platform requirements successfully bypassed for $commandName!</info>", true, IOInterface::NORMAL);
    }

    /**
     * Check if the OS is Windows.
     */
    private function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
