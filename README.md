# GOWA Filament Plugin 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
[![Total Downloads](https://img.shields.io/packagist/dt/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
[![License](https://img.shields.io/packagist/l/gowa-php/filament.svg?style=flat-square)](LICENSE)

Official Filament v5 (and v4 / v3) plugin for managing **GOWA** ([go-whatsapp-web-multidevice](https://github.com/aldinokemal/go-whatsapp-web-multidevice)) WhatsApp instances, connecting via QR Code or 8-digit Pairing Code, and monitoring device connection states directly inside your Filament Panel.

---

## 📦 Requirements

* **PHP**: `>= 8.2`
* **Laravel**: `^10.0 | ^11.0 | ^12.0`
* **Filament**: `^3.0 | ^4.0 | ^5.0`
* **GOWA Laravel Package**: `gowa-php/laravel ^1.0`

---

## 🔧 Installation

Install the package via Composer:

```bash
composer require gowa-php/filament
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag="gowa-filament-config"
```

---

## ⚡ Quick Start

Add `GowaPlugin` to your Filament Panel Provider (e.g., `AdminPanelProvider.php`):

```php
use Gowa\Filament\GowaPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugins([
            GowaPlugin::make(),
        ]);
}
```

---

## ✨ Features

- **📱 GOWA Instance Resource**: View, create, and manage connected WhatsApp devices inside Filament.
- **📷 QR Code Modal & Real-time Polling**: Scan QR codes directly in Filament with automatic status updates (`wire:poll.3s`).
- **🔢 8-Digit Pairing Code**: Link WhatsApp using a phone number with copy-to-clipboard pairing code.
- **📊 Status Widget**: Filament StatsOverview widget tracking Connected, Connecting, and Disconnected instances in real-time.
- **🔌 Disconnect & Refresh Actions**: Disconnect active devices or force-refresh statuses directly from Filament tables.
- **🌐 Multilingual Support**: Built-in English and Portuguese (pt_BR) translations.

---

## 🧪 Testing

Run Pest tests:

```bash
composer test
```

---

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
