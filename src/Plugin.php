<?php

namespace Masgeek;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    /** @var array<string,string> */
    private array $defaultIgnoredExtensions = [
        'ext-pcntl' => '1.0.0',
        'ext-posix' => '1.0.0',
    ];

    /** @var array<string,string> */
    private array $ignoredExtensions = [];

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        if ($this->isWindows()) {
            $this->applyPlatformOverrides();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::INIT => ['onInit'],
        ];
    }

    public function onInit(Event $event): void
    {
        if (!$this->isWindows()) {
            return;
        }

        $this->io->write("<info>➡️  Initializing Windows extension overrides</info>");

        $this->ignoredExtensions = $this->getIgnoredExtensionsFromComposerJson();
        $this->applyPlatformOverrides();
    }

    /**
     * Inject fake extensions into Composer config to bypass missing requirements.
     */
    private function applyPlatformOverrides(): void
    {
        $config = $this->composer->getConfig();
        $platformOverrides = $config->get('platform') ?? [];

        foreach ($this->ignoredExtensions as $ext => $version) {
            if (!isset($platformOverrides[$ext])) {
                $platformOverrides[$ext] = $version;
                $this->io->write("<comment>🛠️  Spoofing missing extension: {$ext}={$version}</comment>");
            } else {
                $this->io->write("<info>✔️  {$ext} already set to {$platformOverrides[$ext]}</info>", true, IOInterface::VERBOSE);
            }
        }

        $config->merge([
            'config' => [
                'platform' => $platformOverrides,
            ],
        ]);

        $this->io->write("<info>✅ Platform config updated successfully.</info>");
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $io->write("<comment>WindowsExtIgnorerPlugin deactivated.</comment>");
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $io->write("<comment>WindowsExtIgnorerPlugin uninstalled.</comment>");
    }

    /**
     * Reads `extra.ignored-extensions` from composer.json and merges with defaults.
     *
     * @return array<string,string>
     */
    private function getIgnoredExtensionsFromComposerJson(): array
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
