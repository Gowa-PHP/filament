# AGENTS.md — Guidelines for AI Coding Agents

## 📌 Project Overview
`gowa-php/filament` is Phase 3 of the GOWA ecosystem — a Filament v5 / v4 / v3 plugin for integrating GOWA WhatsApp instances into Laravel Filament applications.

### 🏗 Architecture & Stack
- **Framework Compatibility**: PHP >= 8.2, Laravel 10/11/12, Filament v3/v4/v5.
- **Dependencies**: `gowa-php/sdk`, `gowa-php/laravel`.
- **Testing Engine**: Pest PHP with `orchestra/testbench`.

### 📂 Directory Structure
- `src/GowaPlugin.php`: Main Filament Plugin class.
- `src/GowaFilamentServiceProvider.php`: Service Provider registering views, translations, and Livewire components.
- `src/Livewire/`: Livewire components (`GowaQrCode`, `GowaPairingCode`).
- `src/Resources/`: Filament resources (`GowaInstanceResource`) and custom actions (`ConnectQrAction`, `ConnectPairingCodeAction`, `DisconnectAction`, `RefreshStatusAction`).
- `src/Widgets/`: Filament widgets (`GowaDeviceStatusWidget`).
- `resources/`: Blade views and translations (`en`, `pt_BR`).
- `tests/`: Pest test suite.

### 🛠 Commandments for Agents
1. Always preserve clean compatibility across PHP 8.2–8.4 and Filament v3–v5.
2. Use Pest PHP for all tests. Never introduce PHPUnit syntax in tests.
3. Keep translations up to date in both `en` and `pt_BR`.
