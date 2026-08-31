# GOWA Filament Plugin 🚀

[![Última Versão no Packagist](https://img.shields.io/packagist/v/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
[![Licença](https://img.shields.io/packagist/l/gowa-php/filament.svg?style=flat-square)](LICENSE)

Plugin oficial do Filament para integração com instâncias de WhatsApp **GOWA** ([go-whatsapp-web-multidevice](https://github.com/aldinokemal/go-whatsapp-web-multidevice)) em aplicações Laravel Filament.

Conecte instâncias via QR Code ou Código de Pareamento de 8 dígitos, envie mensagens em todos os 11 formatos suportados (Texto, Imagens, Documentos/PDFs, Áudio/Voz, Contatos, Localização, Enquetes, etc.) e monitore o status de conexão diretamente no seu painel Filament.

---

## 📦 Requisitos

* **PHP**: `>= 8.2`
* **Laravel**: `^10.0 | ^11.0 | ^12.0`
* **Filament**: `^5.0` (Testado e Validado) | `^3.0 | ^4.0` (Compatibilidade Arquitetural)
* **Pacotes GOWA**: `gowa-php/sdk ^1.0`, `gowa-php/laravel ^1.0`

> [!NOTE]
> Este pacote está **testado e validado no Filament v5**. A compatibilidade com Filament v3 e v4 é mantida em nível arquitetural.

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

- **📱 Resource de Instâncias (`GowaInstanceResource`)**: Visualize, crie e gerencie dispositivos de WhatsApp conectados dentro do Filament.
- **📷 Modal QR Code em Tempo Real**: Escaneie QR codes com atualização automática de status (`wire:poll.3s`).
- **🔢 Código de Pareamento de 8 Dígitos**: Conecte o WhatsApp usando o número de telefone com botão de copiar código.
- **🧪 Playground de Mensagens (`GowaMessagingPage`)**: Tela interativa para testes de envio em todos os 11 tipos de mensagens do GOWA:
  - 💬 **Texto**: Mensagens de texto simples com suporte a resposta (`reply_to`).
  - 🖼️ **Imagem**: Upload de imagem com editor nativo do Filament (corte, rotação, ajustes).
  - 🎥 **Vídeo**: Envio de arquivos de vídeo (`.mp4`, `.avi`, `.mov`).
  - 📄 **Documento / Arquivo**: Envio de PDFs, CSVs, DOCX, XLSX e ZIPs de até 50 MB.
  - 🎙️ **Áudio / Nota de Voz**: Envio de arquivos de áudio ou simulação de áudio gravado (PTT).
  - 🏷️ **Sticker**: Envio de figurinhas em WebP/PNG.
  - 👤 **Cartão de Contato**: Compartilhamento de contatos do WhatsApp.
  - 📍 **Localização**: Envio de coordenadas GPS com nome do local e endereço.
  - 🔗 **Link com Preview**: Envio de links com pré-visualização open-graph.
  - 📊 **Enquete**: Criação de enquetes interativas com múltiplas opções.
  - 📡 **Status de Presença**: Atualização de status digitando (`composing`) ou gravando (`recording`).
- **✉️ Ação Personalizada (`SendGowaMessageAction`)**: Botão de ação para tabelas e formulários que abre modal para envio direto de mensagens via WhatsApp.
- **📊 Widget de Status (`GowaDeviceStatusWidget`)**: Dashboard card exibindo instâncias Conectadas, Conectando e Desconectadas em tempo real.
- **🌐 Suporte Multilíngue**: Tradução nativa em Inglês (`en`) e Português (`pt_BR`).

---

## 💡 Exemplos de Uso

### 1. Adicionando `SendGowaMessageAction` a uma Tabela de Resource

```php
use Gowa\Filament\Actions\SendGowaMessageAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([ ... ])
        ->actions([
            SendGowaMessageAction::make()
                ->numberFrom('telefone'), // Resolve o telefone do registro
        ]);
}
```

---

## 🧪 Testes

Execute a suíte de testes com Pest PHP:

```bash
composer test
```

---

## 📄 Licença

Licença MIT. Consulte [LICENSE](LICENSE) para mais detalhes.
