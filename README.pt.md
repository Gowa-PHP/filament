# GOWA Filament Plugin 🚀

[![Última Versão no Packagist](https://img.shields.io/packagist/v/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
[![Licença](https://img.shields.io/packagist/l/gowa-php/filament.svg?style=flat-square)](LICENSE)

Plugin oficial do Filament v5 (e v4 / v3) para gerenciar instâncias de WhatsApp **GOWA** ([go-whatsapp-web-multidevice](https://github.com/aldinokemal/go-whatsapp-web-multidevice)), conectar aparelhos via QR Code ou Código de Pareamento de 8 dígitos, e monitorar o status de conexão diretamente no painel Filament.

---

## 📦 Requisitos

* **PHP**: `>= 8.2`
* **Laravel**: `^10.0 | ^11.0 | ^12.0`
* **Filament**: `^3.0 | ^4.0 | ^5.0`
* **Pacote GOWA Laravel**: `gowa-php/laravel ^1.0`

---

## 🔧 Instalação

Instale o pacote via Composer:

```bash
composer require gowa-php/filament
```

Publique o arquivo de configuração (opcional):

```bash
php artisan vendor:publish --tag="gowa-filament-config"
```

---

## ⚡ Início Rápido

Registre o `GowaPlugin` no seu Panel Provider do Filament (ex: `AdminPanelProvider.php`):

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

## ✨ Funcionalidades

- **📱 Resource de Instâncias GOWA**: Visualize, crie e gerencie dispositivos de WhatsApp conectados dentro do Filament.
- **📷 Modal QR Code com Polling**: Escaneie QR codes com atualização automática de status (`wire:poll.3s`).
- **🔢 Código de Pareamento de 8 Dígitos**: Conecte o WhatsApp usando o número de telefone com botão de copiar código.
- **📊 Widget de Status**: Card com métricas (StatsOverview) exibindo instâncias Conectadas, Conectando e Desconectadas.
- **🔌 Ações de Desconexão e Atualização**: Desconecte instâncias ativas ou atualize o status diretamente das tabelas do Filament.
- **🌐 Suporte Multilíngue**: Tradução nativa em Inglês e Português (pt_BR).

---

## 🧪 Testes

Execute a suíte de testes com Pest PHP:

```bash
composer test
```

---

## 📄 Licença

Licença MIT. Consulte [LICENSE](LICENSE) para mais detalhes.
