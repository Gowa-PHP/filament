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
* **Pacotes GOWA**: `gowa-php/sdk ^1.1`, `gowa-php/laravel ^1.1`

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

## ⚙️ Configuração de Ambiente (`.env`)

Adicione as credenciais de conexão do servidor GOWA no seu arquivo `.env`:

```env
# Conexão com o Servidor GOWA WhatsApp
GOWA_BASE_URL=https://gowa.suaempresa.com
GOWA_USERNAME=admin
GOWA_PASSWORD=secret
GOWA_TIMEOUT=15

# Configuração de Webhooks (Opcional)
GOWA_WEBHOOK_SECRET=sua_chave_hmac_secret
GOWA_WEBHOOK_PATH=webhooks/gowa
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
- **✉️ Actions Especializadas para Resources**:
  - `SendGowaMessageAction`: Envio rápido de mensagens de texto com templates dinâmicos.
  - `SendGowaDocumentAction`: Envio de PDFs, faturas, contratos ou relatórios via arquivo ou URL.
  - `SendGowaMediaAction`: Envio de imagens, vídeos ou áudios com legendas personalizadas.
- **📊 Widget de Status (`GowaDeviceStatusWidget`)**: Dashboard card exibindo instâncias Conectadas, Conectando e Desconectadas em tempo real.
- **🌐 Suporte Multilíngue**: Tradução nativa em Inglês (`en`) e Português (`pt_BR`).

---

## 💡 Exemplos de Uso das Actions

### 1. Enviar Mensagem Rápida de Texto (`SendGowaMessageAction`)

Adicione uma ação de texto do WhatsApp a qualquer tabela de Resource do Filament:

```php
use Gowa\Filament\Actions\SendGowaMessageAction;

public static function table(Table $table): Table
{
    return $table
        ->columns([ ... ])
        ->actions([
            SendGowaMessageAction::make()
                ->numberFrom('telefone') // Resolve o telefone do registro
                ->message(fn ($record) => "Olá {$record->nome}, seu pedido #{$record->id} foi despachado!"),
        ]);
}
```

### 2. Enviar Documento PDF ou Fatura (`SendGowaDocumentAction`)

Dispare faturas, contratos ou relatórios em PDF para clientes:

```php
use Gowa\Filament\Actions\SendGowaDocumentAction;

SendGowaDocumentAction::make('sendInvoice')
    ->label('Enviar Fatura em PDF')
    ->numberFrom('cliente.telefone')
    ->documentUrl(fn ($record) => $record->pdf_download_url)
    ->filename(fn ($record) => "fatura-{$record->codigo}.pdf");
```

### 3. Enviar Mídia / Fotos / Vídeos (`SendGowaMediaAction`)

Envie comprovantes de pagamento, fotos de produtos ou anexos em vídeo com legenda:

```php
use Gowa\Filament\Actions\SendGowaMediaAction;
use Gowa\Sdk\Dto\MediaType;

SendGowaMediaAction::make('sendReceipt')
    ->label('Enviar Comprovante')
    ->type(MediaType::Image)
    ->numberFrom('cliente_telefone')
    ->mediaFrom('caminho_comprovante')
    ->caption('Segue o seu comprovante de pagamento.');
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
