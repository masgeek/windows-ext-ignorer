<?php

namespace Masgeek;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, EventSubscriberInterface, Capable
{

    /**
     * Extensions to ignore on Windows platforms
     *
     * @var array
     */
    protected array $ignoredExtensions = ['ext-pcntl', 'ext-posix'];

    protected Composer $composer;

    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }


    /**
     * @return string[]
     */
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

    public function deactivate(Composer $composer, IOInterface $io)
    {
        //no implementation
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        //no implementation
    }

    /**
     * @param Event $event
     * @return void
     */
    public function onPreCommandRun(Event $event): void
    {
        // Only apply on Windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return;
        }

        $commandName = $event->getCommandName();
        $this->io->writeError("Command running is $commandName");
        $this->io->write(PHP_EOL . '<options=bold>========= Demo plugin =========</>');
        $this->io->write('<info>Congrats, your plugin works! :)</info>');
        $this->io->write('<options=bold>===============================</>' . PHP_EOL);
    }
}
