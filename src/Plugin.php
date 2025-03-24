<?php

namespace Masgeek;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreCommandRunEvent;

class WindowsExtIgnorerPlugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    /**
     * Extensions to fake on Windows
     */
    protected array $ignoredExtensions = [
        'ext-pcntl' => '1.0.0',
        'ext-posix' => '1.0.0',
    ];

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        // Only run on Windows
        if (!$this->isWindows()) {
            return;
        }

        $this->applyPlatformOverrides();
    }

    public static function getSubscribedEvents(): array
    {
        return [];
        return [
            PluginEvents::PRE_COMMAND_RUN => ['onPreCommandRun', 1],
        ];
    }

    public function onPreCommandRun(PreCommandRunEvent $event): void
    {
        if (!$this->isWindows()) {
            return;
        }

        $command = $event->getCommand();
        $this->io->write("<info>➡️  Running Composer Command: $command</info>");

        $this->applyPlatformOverrides();
    }

    /**
     * Inject fake extensions into Composer config
     */
    protected function applyPlatformOverrides(): void
    {
        $config = $this->composer->getConfig();

        $platformOverrides = $config->get('platform') ?? [];

        foreach ($this->ignoredExtensions as $ext => $version) {
            if (!isset($platformOverrides[$ext])) {
                $platformOverrides[$ext] = $version;
                $this->io->write("<comment>🛠️  Ignoring missing platform requirement: {$ext}={$version}</comment>");
            } else {
                $this->io->write("<info>✔️  {$ext} already set to {$platformOverrides[$ext]}</info>", true, IOInterface::VERBOSE);
            }
        }

        $config->merge([
            'config' => [
                'platform' => $platformOverrides,
            ],
        ]);

        $this->io->write("<info>✅ Platform config updated to spoof missing extensions.</info>");
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // no-op
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // no-op
    }

    private function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
