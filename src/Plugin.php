<?php

namespace Masgeek;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreCommandRunEvent;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    protected array $defaultIgnoredExtensions = [
        'ext-pcntl' => '1.0.0',
        'ext-posix' => '1.0.0',
    ];
    protected array $ignoredExtensions = [];

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
        return [
            PluginEvents::INIT => ['onPreCommandRun', 1],
        ];
    }

    /**
     * Event listener for Composer's PRE_COMMAND_RUN event.
     *
     * @see getSubscribedEvents() for registration
     *
     */
    public function onPreCommandRun(Event $event): void
    {
        if (!$this->isWindows()) {
            return;
        }

        $command = $event->getCommand();
        $this->io->write("<info>➡️  Running Composer Command: $command</info>");

        // Get merged extensions (defaults + composer.json extra)
        $this->ignoredExtensions = $this->getIgnoredExtensionsFromComposerJson();

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

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $this->io->write("<comment>WindowsExtIgnorerPlugin deactivated.</comment>");
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $this->io->write("<comment>WindowsExtIgnorerPlugin uninstalled.</comment>");

    }

    /**
     * Reads `extra` config from composer.json and merges it with defaults.
     */
    protected function getIgnoredExtensionsFromComposerJson(): array
    {
        $extraConfig = $this->composer->getPackage()->getExtra();
        $composerJsonExtensions = [];

        if (!empty($extraConfig['ignored-extensions'])) {
            $composerJsonExtensions = $extraConfig['ignored-extensions'];
            $this->io->write("<comment>🔧 Found ignored-extensions in composer.json:</comment> " . json_encode($composerJsonExtensions));
        }

        return array_merge($this->defaultIgnoredExtensions, $composerJsonExtensions);
    }

    private function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
