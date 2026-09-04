<div align="center">
  <img src="art/gowa-filament-cover.png" alt="gowa-filament Banner" width="100%" max-width="800">

  # gowa-php/filament

  **Plugin Filament v5 / v4 / v3 para integração de instâncias WhatsApp GOWA em aplicações Laravel Filament**

  [![Última Versão](https://img.shields.io/packagist/v/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
  [![Total de Downloads](https://img.shields.io/packagist/dt/gowa-php/filament.svg?style=flat-square)](https://packagist.org/packages/gowa-php/filament)
  [![Licença](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
  [![Versão PHP](https://img.shields.io/badge/PHP-%3E%3D%208.2-777BB4.svg?style=flat-square)](https://php.net)
  [![Laravel](https://img.shields.io/badge/Laravel-10%20|%2011%20|%2012-FF2D20.svg?style=flat-square)](https://laravel.com)
  [![Filament](https://img.shields.io/badge/Filament-v3%20|%20v4%20|%20v5-FDAE4B.svg?style=flat-square)](https://filamentphp.com)

</div>

---

> 🇬🇧 For English documentation, please read [README.md](README.md).

---

## ⚡ Agradecimentos e Dependências do Ecossistema

Este pacote é a Fase 3 do ecossistema GOWA PHP e interage com o ecossistema open-source em Go:

- **[whatsmeow](https://go.mau.fi/whatsmeow)** — Biblioteca Go desenvolvida por [Tulir Asokan](https://github.com/tulir) que realiza a engenharia reversa do protocolo WebSocket do WhatsApp Web Multi-Device e criptografia Signal.
- **[go-whatsapp-web-multidevice (GOWA)](https://github.com/aldinokemal/go-whatsapp-web-multidevice)** — Servidor REST API criado por [Aldino Kemal](https://github.com/aldinokemal) expondo o `whatsmeow` via HTTP e Webhooks.
- **[gowa-php/sdk](https://packagist.org/packages/gowa-php/sdk)** — SDK PHP puro para a API REST do GOWA e parse de Webhooks.
- **[gowa-php/laravel](https://packagist.org/packages/gowa-php/laravel)** — Integração Laravel com Facades, Canais de Notificação, rotas de Webhook e modelos Eloquent.

---

## 📦 Pré-requisitos e Requisitos

* **Servidor GOWA em Execução**: Uma instância ativa do [go-whatsapp-web-multidevice (GOWA)](https://github.com/aldinokemal/go-whatsapp-web-multidevice). A variável `GOWA_BASE_URL` é **obrigatória** no seu arquivo `.env`.
* **PHP**: `>= 8.2`
* **Laravel**: `^10.0 | ^11.0 | ^12.0`
* **Filament**: `^3.0 | ^4.0 | ^5.0`
* **Pacotes GOWA**: `gowa-php/sdk ^1.0`, `gowa-php/laravel ^1.1`

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

## ⚙️ Configuração do Ambiente (`.env`)

Adicione as credenciais de conexão do servidor GOWA ao seu arquivo `.env`:

```env
# Conexão com o Servidor WhatsApp GOWA
GOWA_BASE_URL=https://gowa.suaempresa.com
GOWA_USERNAME=admin
GOWA_PASSWORD=secret
GOWA_TIMEOUT=15

# Configuração de Webhook (Opcional)
GOWA_WEBHOOK_SECRET=sua_secret_hmac
GOWA_WEBHOOK_PATH=webhooks/gowa
```

---

## ⚡ Início Rápido

Adicione o `GowaPlugin` ao seu Provider de Painel do Filament (ex: `AdminPanelProvider.php`):

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

## ✨ Principais Funcionalidades

- **💬 WhatsApp Inbox & Chat em Tempo Real (`GowaConversationsPage`)**: Caixa de entrada bidirecional completa dentro do Filament com busca de contatos, filtro de não lidas, indicadores de status (enviado, entregue, lido com duplo tique azul), modal de envio de mídias e documentos, e alternador instantâneo entre instâncias conectadas.
- **📱 Recurso de Instâncias GOWA (`GowaInstanceResource`)**: Visualize, crie, edite e gerencie instâncias WhatsApp conectadas com modais em slide-over e Infolists nativos do Filament.
- **🔗 Sincronização de Webhook & Gerador de Secret**: Sincronize a URL do webhook e o segredo HMAC diretamente com o servidor GOWA Go sem desconectar. Inclui ação com gerador de segredo aleatório de 32 caracteres.
- **📷 Modal com QR Code em Tempo Real**: Escaneie o QR Code diretamente no Filament com polling automático (`wire:poll.3s`).
- **🔢 Modal de Pareamento via Código de 8 Dígitos**: Conecte o WhatsApp informando o número de telefone com cópia do código com um clique.
- **🧪 Playground de Teste de Mensagens (`GowaMessagingPage`)**: Central interativa com suporte aos 11 formatos de mensagens do GOWA:
  - 💬 **Texto**: Mensagens de texto puro com suporte a resposta direcionada (reply).
  - 🖼️ **Imagem**: Upload de imagem com o Editor Nativo de Imagens do Filament (cortar, rotacionar, inverter).
  - 🎥 **Vídeo**: Upload de vídeos (`.mp4`, `.avi`, `.mov`).
  - 📄 **Documento / Arquivo**: Envio de PDFs, CSVs, DOCX, XLSX e arquivos ZIP de até 50MB.
  - 🎙️ **Áudio / Nota de Voz**: Envio de áudios comuns ou simulados como nota de voz (PTT).
  - 🏷️ **Figurinha (Sticker)**: Envio de figurinhas WebP/PNG.
  - 👤 **Cartão de Contato**: Compartilhamento de contatos do WhatsApp.
  - 📍 **Localização**: Envio de coordenadas GPS com nome do local e endereço.
  - 🔗 **Preview de Link**: Envio de links com preview Open-Graph automático.
  - 📊 **Enquete**: Criação de enquetes interativas com múltiplas opções.
  - 📡 **Status de Presença**: Atualização do status digitando (`composing`) ou gravando (`recording`).
- **⚡ Actions Fluentes e Facade**: Envio intuitivo e expressivo de mensagens com encadeamento de métodos, seja via Facade Fluente do Laravel (`Gowa::to()->from()->text()->send()`) ou através da Action nativa do Filament (`SendGowaAction::make()->to()->from()->text()->direct()`). Suporta disparo direto no clique ou revisão em modal interativo.
- **✉️ Ações Especializadas para Recursos**:
  - `SendGowaAction`: Action fluente unificada compatível com texto, imagens, vídeos, áudio, notas de voz, documentos, enquetes, contatos, localizações e callbacks fluentes customizados.
  - `SendGowaMessageAction`: Envio rápido de mensagens de texto com templates dinâmicos e suporte a modal.
  - `SendGowaDocumentAction`: Envio de PDFs, faturas, contratos ou planilhas via caminho de arquivo ou URL.
  - `SendGowaMediaAction`: Envio de imagens, vídeos ou áudios com legendas personalizadas.
  - `UpdateWebhookAction`: Sincronização instantânea da URL e segredo HMAC no backend GOWA.
- **📊 Widget de Status em Tempo Real (`GowaDeviceStatusWidget`)**: Card de dashboard exibindo instâncias Conectadas, Conectando e Offline.
- **🌐 Suporte Multilíngue**: Traduções nativas em Inglês (`en`) e Português do Brasil (`pt_BR`).

---

## 📸 Capturas de Tela

### WhatsApp Inbox & Chat em Tempo Real

<p align="center">
  <img src="art/inbox-widget.png" alt="WhatsApp Live Inbox & Chat no Filament" width="100%">
</p>

### Gerenciamento de Instâncias WhatsApp

<p align="center">
  <img src="art/instances-list.png" alt="Lista de instâncias do WhatsApp no Filament" width="100%">
</p>

### Modal de Conexão via QR Code

<p align="center">
  <img src="art/qr-modal.png" alt="Modal com QR Code do WhatsApp no Filament" width="100%">
</p>

### Central de Teste de Mensagens

<p align="center">
  <img src="art/messaging-center.png" alt="Central de mensagens do WhatsApp no Filament" width="100%">
</p>

---

## 💡 Exemplos de Uso das Actions do Filament e API Fluente

### 1. Action Fluente (`SendGowaAction`)

A `SendGowaAction` oferece uma API encadeável e fluente direto nas suas tabelas e formulários Filament:

#### Disparo Direto (Sem Modal):

```php
use Gowa\Filament\Actions\SendGowaAction;

SendGowaAction::make()
    ->to(fn ($record) => $record->phone_number)
    ->text(fn ($record) => "Olá {$record->name}, seu pedido #{$record->id} foi confirmado com sucesso!")
    ->direct(); // Envia imediatamente ao clicar
```

#### Confirmação com Modal e Formulário Pré-preenchido:

```php
use Gowa\Filament\Actions\SendGowaAction;

SendGowaAction::make('contactCustomer')
    ->label('Conversar no WhatsApp')
    ->to(fn ($record) => $record->phone)
    ->text(fn ($record) => "Olá {$record->first_name}, tudo bem? Sobre sua solicitação...");
    // Abre um modal nativo do Filament permitindo que o operador revise ou edite a mensagem antes de enviar
```

#### Envio Fluente de Documentos, Faturas e Imagens:

```php
// Envio de Fatura em PDF
SendGowaAction::make('sendInvoice')
    ->to(fn ($record) => $record->customer_phone)
    ->document(fn ($record) => $record->pdf_url, filename: 'fatura.pdf')
    ->direct();

// Envio de Imagem / Comprovante
SendGowaAction::make('sendReceipt')
    ->to(fn ($record) => $record->phone)
    ->image(fn ($record) => $record->receipt_url, caption: 'Comprovante de pagamento')
    ->direct();
```

#### Envio Customizado com Callback Fluente (`->fluent()`):

```php
use Gowa\Filament\Actions\SendGowaAction;
use Gowa\Laravel\PendingMessage;

SendGowaAction::make('sendFeedbackPoll')
    ->fluent(fn (PendingMessage $msg, $record) => $msg
        ->to($record->phone)
        ->from($record->device_id)
        ->poll('Como você avalia nosso atendimento?', ['Excelente', 'Bom', 'Regular', 'Ruim'])
    )
    ->direct();
```

### 2. Usando a Facade Fluente do Laravel em Qualquer Lugar do Filament

Você também pode utilizar a Facade fluente do `gowa-php/laravel` em actions customizadas, lifecycles de formulários ou bulk actions:

```php
use Gowa\Laravel\Facades\Gowa;

// Envio fluente de mensagem de texto
Gowa::to($record->phone)
    ->from($record->device_id)
    ->text("O status do seu pedido foi atualizado para {$record->status}.")
    ->send();

// Envio fluente de documento
Gowa::to($record->phone)
    ->document(Storage::path('invoices/fatura-1001.pdf'))
    ->send();
```

### 3. Ações Especializadas com Modal

#### Envio de Mensagem de Texto (`SendGowaMessageAction`)

```php
use Gowa\Filament\Actions\SendGowaMessageAction;

SendGowaMessageAction::make()
    ->to(fn ($record) => $record->phone_number)
    ->text(fn ($record) => "Olá {$record->name}, seu pedido #{$record->id} foi enviado!");
```

#### Envio de Documento PDF ou Fatura (`SendGowaDocumentAction`)

```php
use Gowa\Filament\Actions\SendGowaDocumentAction;

SendGowaDocumentAction::make('sendInvoice')
    ->label('Enviar Fatura em PDF')
    ->to(fn ($record) => $record->customer_phone)
    ->document(fn ($record) => $record->pdf_download_url, filename: fn ($record) => "fatura-{$record->code}.pdf");
```

#### Envio de Mídias / Fotos / Vídeos (`SendGowaMediaAction`)

```php
use Gowa\Filament\Actions\SendGowaMediaAction;
use Gowa\Sdk\Dto\MediaType;

SendGowaMediaAction::make('sendReceipt')
    ->label('Enviar Comprovante')
    ->type(MediaType::Image)
    ->to(fn ($record) => $record->client_phone)
    ->mediaFrom('receipt_path')
    ->caption('Aqui está o seu comprovante de pagamento.');
```

---

## 🧪 Testes

Execute a suíte de testes do Pest PHP:

```bash
composer test
```

---

## ⚠️ Isenção de Responsabilidade e Termos de Uso (Disclaimer)

Este software é uma biblioteca de código aberto desenvolvida **exclusivamente para fins educacionais, de pesquisa e ambiente de testes**.

- **Termos de Serviço de Terceiros**: Os usuários desta biblioteca são inteiramente responsáveis por cumprir os Termos de Serviço do WhatsApp, as Políticas de Plataforma da Meta e os termos de quaisquer serviços de terceiros utilizados.
- **Mensagens Automatizadas e Conformidade**: O envio de mensagens automatizadas ou não autorizadas pode violar os termos da plataforma. Os usuários devem garantir a conformidade com as leis de privacidade aplicáveis (ex: LGPD, GDPR), consentimento prévio dos destinatários e diretrizes oficiais.
- **Isenção de Garantia e Responsabilidade**: Este software é fornecido "como está", sem garantias de qualquer tipo. Os autores e contribuidores não assumem qualquer responsabilidade por banimentos de conta, perda de dados, interrupções de serviço ou mau uso desta biblioteca.

---

## 📄 Licença

Licença MIT. Consulte [LICENSE](LICENSE) para obter mais informações.
