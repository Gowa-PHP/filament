# AGENTS.md — Guidelines for AI Coding Agents

## 📌 Project Overview
`gowa-php/filament` is the official Filament v3/v4/v5 plugin for integrating GOWA WhatsApp instances into Laravel Filament applications.

### 🏗 Architecture & Stack
- **Framework Compatibility**: PHP >= 8.2, Laravel 10/11/12, Filament v3/v4/v5.
- **Dependencies**: `gowa-php/sdk`, `gowa-php/laravel`.
- **Testing Engine**: Pest PHP 3 with `orchestra/testbench`.
- **Autoload**: PSR-4 — `Gowa\Filament\` → `src/`, `Gowa\Filament\Tests\` → `tests/`.
- **Auto-discovery**: Service Provider registered via `composer.json` → `extra.laravel.providers`.

### 📂 Directory Structure
- `src/GowaPlugin.php`: Main Filament Plugin class (implements `Filament\Contracts\Plugin`, ID `'gowa-filament'`). Provides 4 toggleable feature flags: `instanceResource()`, `messagingPage()`, `conversationsPage()`, `deviceStatusWidget()` — all enabled by default.
- `src/GowaFilamentServiceProvider.php`: Service Provider registering views, translations, Livewire components, and publishable assets (`gowa-filament-config`, `gowa-filament-views`, `gowa-filament-translations`).
- `src/Livewire/`: Livewire components with polling (`GowaQrCode`, `GowaPairingCode`).
- `src/Pages/`: Custom Filament pages (`GowaMessagingPage` — messaging test sandbox, `GowaConversationsPage` — real-time inbox and chat).
- `src/Actions/`: Reusable Filament table/form actions (`SendGowaMessageAction`, `SendGowaDocumentAction`, `SendGowaMediaAction`, `SendGowaNotificationAction`).
- `src/Resources/`: Filament resources (`GowaInstanceResource` — table, form, infolist) and resource-specific actions (`ConnectQrAction`, `ConnectPairingCodeAction`, `DisconnectAction`, `RefreshStatusAction`, `UpdateWebhookAction`). Resource pages: `ListGowaInstances`, `CreateGowaInstance`, `EditGowaInstance`, `ViewGowaInstance`.
- `src/Widgets/`: Filament widgets (`GowaDeviceStatusWidget` — dashboard counts of connected/disconnected instances).
- `resources/views/`: Blade views organized in `actions/`, `livewire/`, `pages/`.
- `resources/lang/`: Translations in `en` and `pt_BR`.
- `config/gowa-filament.php`: Plugin configuration.
- `tests/`: Pest test suite with `TestCase.php` base class.
- `examples/`: Reference `AdminPanelProvider.php` and setup instructions.

### ⚙️ Configuration (`config/gowa-filament.php`)
- `navigation.group` (`'WhatsApp'`), `navigation.sort` (`1`), `navigation.icon`, `navigation.should_register_navigation`.
- `model` — Eloquent model for instances (default: `\Gowa\Laravel\Models\GowaInstance::class`).
- `polling.qr_code_interval` and `polling.pairing_code_interval` — Livewire polling intervals in seconds (default: `3`).

### 🧪 Test Infrastructure
- **Base class**: `tests/TestCase.php` extends `Orchestra\Testbench\TestCase`.
- **Database**: SQLite `:memory:` with `gowa_instances`, `gowa_conversations`, `gowa_messages` tables created in `setUp()`.
- **Panel**: Creates and registers a Filament Panel `'admin'` with `GowaPlugin::make()`.
- **Providers loaded**: Full Filament stack (Support, Forms, Actions, Notifications, Widgets, Filament) + Livewire + BladeUI Icons + `GowaServiceProvider` + `GowaFilamentServiceProvider`.
- **Config mock**: `gowa.base_url`, `gowa.auth.username`, `gowa.auth.password`, `gowa.webhook.secret`.
- **Pest binding**: `uses(TestCase::class)->in('Feature')` in `tests/Pest.php`.
- **Test files**: `PluginTest`, `GowaInstanceResourceTest`, `GowaMessagingPageTest`, `GowaConversationsPageTest`, `LivewireQrCodeTest`, `LivewirePairingCodeTest`, `SendGowaActionsTest`.

### 🚀 CI Pipeline (`.github/workflows/ci.yml`)
- **Triggers**: push to `main`, tags `v*`, PRs against `main`.
- **Matrix**: PHP 8.3 + 8.4 × Laravel 12.
- **Command**: `vendor/bin/pest`.
- **Concurrency**: grouped by workflow + ref, `cancel-in-progress: false`.

### 🏷 Versioning
- Follows **semver** with incremental releases via **git tags** (`v*`) on the `main` branch.
- Branch alias: `dev-main` → `1.x-dev` (defined in `composer.json` → `extra.branch-alias`).

### 🛠 Commandments for Agents
1. **Compatibility**: Always preserve clean compatibility across PHP 8.2–8.4, Laravel 10–12, and Filament v3–v5.
2. **Testing**: Use Pest PHP for all tests (`vendor/bin/pest`). Never introduce PHPUnit syntax in tests.
3. **Translations**: Keep translations up to date in both `en` and `pt_BR` (`resources/lang/en/gowa-filament.php` and `resources/lang/pt_BR/gowa-filament.php`).
4. **Git Operations**: NEVER perform `git commit`, `git tag`, or `git push` without explicit user permission and confirmation.
5. **Form Dehydration**: Non-column or calculated form fields (such as `webhook_url`) MUST include `->dehydrated(false)` to prevent Eloquent missing column database errors.
6. **Cross-Version Filament Actions**: In form component actions/suffix actions, use `Filament\Actions\Action` and un-typehinted `$set` closure parameters (`function ($set)`) to ensure cross-version compatibility between Filament v3, v4, and v5.
