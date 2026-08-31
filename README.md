# GOWA Filament Plugin 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
[![Total Downloads](https://img.shields.io/packagist/dt/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
[![License](https://img.shields.io/packagist/l/gowa-php/filament.svg?style=flat-square)](LICENSE)

Official Filament plugin for integrating **GOWA** ([go-whatsapp-web-multidevice](https://github.com/aldinokemal/go-whatsapp-web-multidevice)) WhatsApp instances into Laravel Filament applications. 

Connect instances via QR Code or 8-digit Pairing Code, send messages across 11 supported formats (Text, Images, Documents/PDFs, Audio/Voice, Contacts, Locations, Polls, etc.), and monitor connection states directly inside your Filament Panel.

---

## 📦 Requirements

* **PHP**: `>= 8.2`
* **Laravel**: `^10.0 | ^11.0 | ^12.0`
* **Filament**: `^5.0` (Tested & Verified) | `^3.0 | ^4.0` (Architectural Compatibility)
* **GOWA Packages**: `gowa-php/sdk ^1.1`, `gowa-php/laravel ^1.1`

> [!NOTE]
> This package is currently **tested and verified on Filament v5**. Support for Filament v3 and v4 is maintained at the architecture level.

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

## ✨ Key Features

- **📱 GOWA Instance Resource (`GowaInstanceResource`)**: View, create, and manage connected WhatsApp instances.
- **📷 Real-Time QR Code Modal**: Scan QR codes directly in Filament with automatic polling (`wire:poll.3s`).
- **🔢 8-Digit Pairing Code Modal**: Link WhatsApp using a phone number with copy-to-clipboard pairing code.
- **🧪 Messaging Test Sandbox (`GowaMessagingPage`)**: Interactive playground supporting all 11 GOWA message formats:
  - 💬 **Text**: Plain text messages with reply targeting.
  - 🖼️ **Image**: Image upload with native Filament Image Editor (crop, rotate, flip).
  - 🎥 **Video**: Video file uploads (`.mp4`, `.avi`, `.mov`).
  - 📄 **Document / File**: Upload PDFs, CSVs, DOCX, XLSX, ZIP archives up to 50MB.
  - 🎙️ **Audio / Voice Note**: Send audio files or simulate voice notes (PTT).
  - 🏷️ **Sticker**: Send WebP/PNG stickers.
  - 👤 **Contact Card**: Share WhatsApp contact cards.
  - 📍 **Location**: Share GPS coordinates with location names and addresses.
  - 🔗 **Link Preview**: Send links with automated open-graph previews.
  - 📊 **Poll**: Interactive multi-option voting polls.
  - 📡 **Presence Status**: Update typing (`composing`) or recording (`recording`) status.
- **✉️ Specialized Resource Actions**:
  - `SendGowaMessageAction`: Quick text messages with dynamic templates.
  - `SendGowaDocumentAction`: Send PDFs, invoices, contracts, or spreadsheets via file path or URL.
  - `SendGowaMediaAction`: Send images, videos, or audio with custom captions.
- **📊 Real-time Status Widget (`GowaDeviceStatusWidget`)**: Dashboard card displaying Connected, Connecting, and Offline instances.
- **🌐 Multilingual Support**: Built-in English (`en`) and Portuguese (`pt_BR`) translations.

---

## 💡 Specialized Actions Usage Examples

### 1. Send Quick Text Message (`SendGowaMessageAction`)

Add a WhatsApp text action to any Filament Resource table:

```php
use Gowa\Filament\Actions\SendGowaMessageAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([ ... ])
        ->actions([
            SendGowaMessageAction::make()
                ->numberFrom('phone_number') // Resolves recipient phone from record
                ->message(fn ($record) => "Hello {$record->name}, your order #{$record->id} has been shipped!"),
        ]);
}
```

### 2. Send PDF Document or Invoice (`SendGowaDocumentAction`)

Easily dispatch invoices, contracts, or report PDFs to customers:

```php
use Gowa\Filament\Actions\SendGowaDocumentAction;

SendGowaDocumentAction::make('sendInvoice')
    ->label('Send Invoice PDF')
    ->numberFrom('customer.phone')
    ->documentUrl(fn ($record) => $record->pdf_download_url)
    ->filename(fn ($record) => "invoice-{$record->code}.pdf");
```

### 3. Send Media / Photos / Videos (`SendGowaMediaAction`)

Send image receipts, product photos, or video attachments with custom captions:

```php
use Gowa\Filament\Actions\SendGowaMediaAction;
use Gowa\Sdk\Dto\MediaType;

SendGowaMediaAction::make('sendReceipt')
    ->label('Send Payment Receipt')
    ->type(MediaType::Image)
    ->numberFrom('client_phone')
    ->mediaFrom('receipt_path')
    ->caption('Here is your payment confirmation receipt.');
```

---

## 🧪 Testing

Run Pest PHP tests:

```bash
composer test
```

---

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
