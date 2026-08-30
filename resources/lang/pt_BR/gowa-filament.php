<?php

return [
    'navigation' => [
        'group' => 'WhatsApp',
        'label' => 'Instâncias WhatsApp',
        'plural_label' => 'Instâncias WhatsApp',
    ],
    'status' => [
        'connected' => 'Conectado',
        'connecting' => 'Conectando',
        'disconnected' => 'Desconectado',
        'unknown' => 'Desconhecido',
    ],
    'actions' => [
        'connect_qr' => 'Conectar via QR Code',
        'connect_code' => 'Conectar via Código',
        'disconnect' => 'Desconectar',
        'refresh_status' => 'Atualizar Status',
        'disconnect_confirm' => 'Tem certeza de que deseja desconectar este aparelho?',
    ],
    'notifications' => [
        'disconnected_success' => 'Dispositivo desconectado com sucesso.',
        'status_refreshed' => 'Status do dispositivo atualizado.',
        'error_occurred' => 'Ocorreu um erro ao comunicar com o servidor GOWA.',
    ],
    'qr' => [
        'title' => 'Escanear QR Code',
        'instructions' => 'Abra o WhatsApp no celular, vá em Configurações > Aparelhos conectados e escaneie este QR code.',
        'waiting' => 'Aguardando leitura...',
        'connected' => 'Dispositivo conectado com sucesso!',
        'refresh' => 'Atualizar QR Code',
        'expired' => 'QR Code expirado. Clique para atualizar.',
    ],
    'pairing' => [
        'title' => 'Conectar com Número de Telefone',
        'phone_label' => 'Número de Telefone (com DDD e DDI)',
        'phone_placeholder' => 'Ex: 5511999999999',
        'generate_code' => 'Gerar Código de Pareamento',
        'code_title' => 'Seu Código de Pareamento',
        'code_instructions' => 'Insira este código no WhatsApp do seu celular (Aparelhos conectados > Conectar com número de telefone).',
        'copy' => 'Copiar Código',
        'copied' => 'Copiado!',
        'waiting' => 'Aguardando confirmação do aparelho...',
    ],
    'widgets' => [
        'total_instances' => 'Total de Instâncias',
        'connected_instances' => 'Conectadas',
        'connecting_instances' => 'Conectando',
        'disconnected_instances' => 'Desconectadas',
    ],
];
