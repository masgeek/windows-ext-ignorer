# Windows Extension Ignorer for Composer

A Composer plugin that automatically ignores platform requirements like `ext-pcntl` and `ext-posix` on Windows systems.

---

## 🚧 The Problem

Many PHP packages require extensions such as `ext-pcntl` and `ext-posix`—extensions that are **not available** on Windows. As a Windows developer, you're often forced to:

1. Add the `--ignore-platform-reqs` flag to every Composer command  
2. Set up custom command aliases  
3. Modify `composer.json` manually to workaround the missing extensions  

---

## ✅ The Solution

This plugin **automatically** detects when you're on a Windows system and **spoofs** the required extensions for you—no flags, no aliases, no extra steps.

It works **out of the box** with sensible defaults (ignoring `ext-pcntl` and `ext-posix`), and you can also **customize** which extensions you want to ignore via `composer.json`!

---

## ⚠️ Production Environment Disclaimer

> **Important:**  
> This plugin is designed for **development environments on Windows** only.  
> It should **not** be used in production environments, staging servers, or CI pipelines that require accurate platform checks.  
>  
> Ignoring required PHP extensions may cause runtime errors or unexpected behavior in your application. Always ensure your production environment meets the actual extension requirements of your packages.

---

## ⚙️ Installation

It’s recommended to install the plugin as a **development dependency**, since it’s only useful in development environments:

```bash
composer require --dev masgeek/windows-ext-ignorer
```

---

## 🚀 Usage

Once installed, the plugin **just works** on Windows.

When running any Composer command:
1. The plugin detects that you're on Windows  
2. It automatically **injects** the specified extensions into Composer’s platform config  
3. Composer proceeds without showing errors about missing extensions  

---

## 🔧 Customizing Ignored Extensions

By default, the plugin injects these extensions into Composer’s `platform` config:
- `ext-pcntl`: `1.0.0`
- `ext-posix`: `1.0.0`

If you want to ignore additional (or different) extensions, you can specify them in your `composer.json` file under the `extra` section.

### Example `composer.json` Configuration:
```json
{
  "extra": {
    "ignored-extensions": {
      "ext-pcntl": "1.0.0",
      "ext-posix": "1.0.0",
      "ext-sockets": "1.0.0"
    }
  }
}
```

You can specify **any** extensions that are not supported or available in your Windows environment.

---

## 🖥️ Verbose Output

To see the plugin in action, run Composer with the `-v` or `-vvv` flag:

```bash
composer require some/package -vvv
```

Example output:

```
➡️  Running Composer Command: require
🔧 Found ignored-extensions in composer.json: {"ext-pcntl":"1.0.0","ext-posix":"1.0.0","ext-sockets":"1.0.0"}
🛠️  Ignoring missing platform requirement: ext-pcntl=1.0.0
🛠️  Ignoring missing platform requirement: ext-posix=1.0.0
🛠️  Ignoring missing platform requirement: ext-sockets=1.0.0
✅ Platform config updated to spoof missing extensions.
```

---

## ⚙️ How It Works

1. Subscribes to Composer’s `pre-command-run` event  
2. Detects if the OS is Windows  
3. If true, it merges default and `composer.json` configured ignored extensions  
4. It updates Composer’s `platform` config at runtime  
5. After Composer runs, the overrides are cleared (no changes are persisted to your files)

---

## ❓ FAQ

**Q:** Will this modify my `composer.json`?  
**A:** No. It temporarily adjusts Composer’s internal configuration and does **not** write to your `composer.json` file.

**Q:** Can I use this on Linux or Mac?  
**A:** No. The plugin only activates on Windows (`PHP_OS_FAMILY === 'Windows'`). On other operating systems, it does nothing.

---

## 📝 License

MIT License  
Author: Sammy Barasa  
