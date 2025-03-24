# Windows Extension Ignorer for Composer

A Composer plugin that automatically ignores `ext-pcntl` and `ext-posix` platform requirements on Windows systems.

## The Problem

Many PHP packages require extensions like `ext-pcntl` and `ext-posix` which are not available on Windows. This forces Windows users to:

1. Manually add the `--ignore-platform-reqs` flag to every Composer command
2. Set up command aliases
3. Modify their `composer.json` to exclude these requirements

## The Solution

This plugin automatically detects when you're on a Windows system and ignores the `ext-pcntl` and `ext-posix` platform requirements for you. No manual flags or aliases needed!

## Installation

```bash
composer require masgeek/windows-ext-ignorer
```

## Usage

Once installed, the plugin works automatically. When running Composer commands on a Windows system:

1. The plugin detects you're on Windows
2. It automatically removes `ext-pcntl` and `ext-posix` from the platform requirements
3. You can install packages normally without seeing errors about missing extensions

## Verbose Output

If you want to see when the plugin is active, run your Composer commands with the `-v` flag:

```bash
composer require some/package -v
```

You'll see messages like:

```
WindowsExtIgnorer: Automatically ignoring ext-pcntl requirement on Windows
WindowsExtIgnorer: Automatically ignoring ext-posix requirement on Windows
WindowsExtIgnorer: Platform requirements modified for Windows
```

## How It Works

The plugin:

1. Subscribes to Composer's `pre-pool-create` event
2. Checks if the current system is Windows
3. If on Windows, it removes the specified extensions from the platform repository
4. This allows packages that require these extensions to install normally

## Customization

If you want to ignore additional extensions, you can fork this plugin and modify the `$ignoredExtensions` array in the `WindowsExtIgnorerPlugin` class.

## License

MIT