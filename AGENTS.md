# AGENTS.md — Guidelines for AI Coding Agents

## 📌 Project Overview
`gowa-php/filament` is the official Filament v5 / v4 / v3 plugin for integrating GOWA WhatsApp instances into Laravel Filament applications.

### 🏗 Architecture & Stack
- **Framework Compatibility**: PHP >= 8.2, Laravel 10/11/12, Filament v3/v4/v5.
- **Dependencies**: `gowa-php/sdk`, `gowa-php/laravel`.
- **Testing Engine**: Pest PHP with `orchestra/testbench`.

### 📂 Directory Structure
- `src/GowaPlugin.php`: Main Filament Plugin class.
- `src/GowaFilamentServiceProvider.php`: Service Provider registering views, translations, and Livewire components.
- `src/Livewire/`: Livewire components (`GowaQrCode`, `GowaPairingCode`).
- `src/Pages/`: Custom Filament pages (`GowaMessagingPage` messaging test sandbox).
- `src/Actions/`: Reusable Filament table/form actions (`SendGowaMessageAction`, `SendGowaDocumentAction`, `SendGowaMediaAction`, `SendGowaNotificationAction`).
- `src/Resources/`: Filament resources (`GowaInstanceResource`) and resource-specific actions (`ConnectQrAction`, `ConnectPairingCodeAction`, `DisconnectAction`, `RefreshStatusAction`, `UpdateWebhookAction`).
- `src/Widgets/`: Filament widgets (`GowaDeviceStatusWidget`).
- `resources/`: Blade views (`actions/`, `livewire/`, `pages/`) and translations (`en`, `pt_BR`).
- `tests/`: Pest test suite.

### 🛠 Commandments for Agents
1. **Compatibility**: Always preserve clean compatibility across PHP 8.2–8.4, Laravel 10–12, and Filament v3–v5.
2. **Testing**: Use Pest PHP for all tests (`vendor/bin/pest`). Never introduce PHPUnit syntax in tests.
3. **Translations**: Keep translations up to date in both `en` and `pt_BR` (`resources/lang/en/gowa-filament.php` and `resources/lang/pt_BR/gowa-filament.php`).
4. **Git Operations**: NEVER perform `git commit`, `git tag`, or `git push` without explicit user permission and confirmation.
5. **Form Dehydration**: Non-column or calculated form fields (such as `webhook_url`) MUST include `->dehydrated(false)` to prevent Eloquent missing column database errors.
6. **Cross-Version Filament Actions**: In form component actions/suffix actions, use `Filament\Actions\Action` and un-typehinted `$set` closure parameters (`function ($set)`) to ensure cross-version compatibility between Filament v3, v4, and v5.
