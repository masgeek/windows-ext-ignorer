<?php

namespace Masgeek;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Repository\PlatformRepository;
use Composer\Plugin\Capable;

class Plugin implements PluginInterface, EventSubscriberInterface, Capable
{
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Extensions to ignore on Windows platforms
     *
     * @var array
     */
    protected $ignoredExtensions = ['ext-pcntl', 'ext-posix'];


    /**
     * @return string[]
     */
    public function getCapabilities(): array
    {
        return [
            'Composer\Plugin\Capability\CommandProvider' => 'Masgeek\\CommandProvider'
        ];
    }

    public static function getSubscribedEvents()
    {
        return [
            'pre-pool-create' => 'handlePrePoolCreate',
        ];
    }

    /**
     * Handle the pre-pool-create event to modify platform packages
     *
     * @param PrePoolCreateEvent $event
     * @throws \ReflectionException
     */
    public function handlePrePoolCreate(PrePoolCreateEvent $event)
    {
        // Only apply on Windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return;
        }
        // Get repositories from the event
        $repositories = $event->getRepositories();

        // Find the platform repository
        $platformRepo = null;
        foreach ($repositories as $repo) {
            if ($repo instanceof PlatformRepository) {
                $platformRepo = $repo;
                break;
            }
        }

        if (!$platformRepo) {
            return;
        }

        $packages = $platformRepo->getPackages();

        // Create a list of packages to remove
        $packagesToRemove = [];

        foreach ($packages as $index => $package) {
            $name = $package->getName();
            if (in_array($name, $this->ignoredExtensions)) {
                $packagesToRemove[] = $index;
                $this->io->write(
                    sprintf('<info>WindowsExtIgnorer:</info> Automatically ignoring %s requirement on Windows', $name),
                    true,
                    IOInterface::VERBOSE
                );
            }
        }

        // If we found packages to remove, filter them out
        if (!empty($packagesToRemove)) {
            // Create a new filtered list of packages
            $filteredPackages = [];
            foreach ($packages as $index => $package) {
                if (!in_array($index, $packagesToRemove)) {
                    $filteredPackages[] = $package;
                }
            }

            // Create a replacement repository with the filtered packages
            $filteredRepo = new PlatformRepository([], []);
            $reflectionClass = new \ReflectionClass($filteredRepo);

            $packagesProperty = $reflectionClass->getProperty('packages');
            $packagesProperty->setAccessible(true);
            $packagesProperty->setValue($filteredRepo, $filteredPackages);

            // Replace the original repository in the list
            foreach ($repositories as $key => $repo) {
                if ($repo instanceof PlatformRepository) {
                    $repositories[$key] = $filteredRepo;
                    break;
                }
            }

            // Set the modified repositories back to the event
            $reflectionEvent = new \ReflectionClass($event);
            $reposProperty = $reflectionEvent->getProperty('repositories');
            $reposProperty->setAccessible(true);
            $reposProperty->setValue($event, $repositories);

            $this->io->write('<info>WindowsExtIgnorer:</info> Platform requirements modified for Windows', true, IOInterface::VERBOSE);
        }
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }
}
