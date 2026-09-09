# Contributing to gowa-php/filament

Thank you for considering contributing to `gowa-php/filament`! We welcome contributions, bug reports, and feature suggestions from the community.

## Code of Conduct

Please review and adhere to our [Code of Conduct](CODE_OF_CONDUCT.md) in all project interactions.

## Prerequisites & Framework Compatibility

This package is designed for broad compatibility:
- **PHP**: `>= 8.2` (supporting PHP 8.2, 8.3, and 8.4)
- **Laravel**: `^10.0 | ^11.0 | ^12.0`
- **Filament**: `^4.0 | ^5.0`
- **Testing Engine**: Pest PHP 3 with `orchestra/testbench`

## Development Setup

1. **Fork and clone** the repository:
   ```bash
   git clone https://github.com/Gowa-PHP/filament.git
   cd filament
   ```

2. **Install dependencies** via Composer:
   ```bash
   composer install
   ```

3. **Run the test suite** using Pest PHP:
   ```bash
   vendor/bin/pest
   ```

4. **Run code style checks**:
   ```bash
   composer run lint
   ```

## Development Guidelines

1. **Branch Naming**:
   - Features: `feat/feature-name`
   - Bug fixes: `fix/issue-description`
   - Chores/Docs: `chore/task-name` or `docs/update-description`

2. **Testing**:
   - Write tests using Pest PHP (`tests/Feature/`).
   - Never introduce PHPUnit syntax in tests.
   - Run `vendor/bin/pest` to verify all tests pass before submitting.

3. **Translations**:
   - If adding or changing user-facing strings, update both English (`resources/lang/en/gowa-filament.php`) and Brazilian Portuguese (`resources/lang/pt_BR/gowa-filament.php`).

4. **Filament Form Dehydration & Cross-Version Actions**:
   - Non-column or calculated form fields must include `->dehydrated(false)` to prevent Eloquent database errors.
   - For actions in form components, use `Filament\Actions\Action` and un-typehinted `$set` closure parameters (`function ($set)`) to ensure cross-version compatibility between Filament v4 and v5.

5. **Code Style**:
   - Follow PSR-12 and the project's PHP-CS-Fixer configuration.
   - Run `composer run fix` to automatically format your code.

6. **Submitting a Pull Request**:
   - Open a Pull Request targeting the `main` branch.
   - Fill in the Pull Request template with details about your changes and verification output.

## Security Vulnerabilities

If you discover a security vulnerability, please consult our [Security Policy](SECURITY.md) instead of filing a public issue.
