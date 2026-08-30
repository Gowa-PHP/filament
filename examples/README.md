# GOWA Filament Examples

This directory contains reference implementations for integrating `gowa-php/filament` into your Filament v5 / v4 / v3 application.

## Files Included

- **`AdminPanelProvider.php`**: Example Filament Panel Provider registering `GowaPlugin::make()`.

## Usage

1. Copy `AdminPanelProvider.php` or register `GowaPlugin::make()` inside your existing `app/Providers/Filament/AdminPanelProvider.php`.
2. Access your Filament Panel at `/admin`.
3. Navigate to **WhatsApp > WhatsApp Instances** to create, connect, or manage your GOWA instances via QR Code or Pairing Code.
