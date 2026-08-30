<?php

return [
    'navigation' => [
        'group' => 'WhatsApp',
        'label' => 'WhatsApp Instances',
        'plural_label' => 'WhatsApp Instances',
    ],
    'status' => [
        'connected' => 'Connected',
        'connecting' => 'Connecting',
        'disconnected' => 'Disconnected',
        'unknown' => 'Unknown',
    ],
    'actions' => [
        'connect_qr' => 'Connect via QR Code',
        'connect_code' => 'Connect via Pairing Code',
        'disconnect' => 'Disconnect',
        'refresh_status' => 'Refresh Status',
        'disconnect_confirm' => 'Are you sure you want to disconnect this device?',
    ],
    'notifications' => [
        'disconnected_success' => 'Device disconnected successfully.',
        'status_refreshed' => 'Device status refreshed.',
        'error_occurred' => 'An error occurred while communicating with the GOWA server.',
    ],
    'qr' => [
        'title' => 'Scan QR Code',
        'instructions' => 'Open WhatsApp on your phone, go to Settings > Linked Devices, and scan this QR code.',
        'waiting' => 'Waiting for scan...',
        'connected' => 'Device connected successfully!',
        'refresh' => 'Refresh QR Code',
        'expired' => 'QR Code expired. Click to refresh.',
    ],
    'pairing' => [
        'title' => 'Link with Phone Number',
        'phone_label' => 'Phone Number (with country code)',
        'phone_placeholder' => 'e.g. 5511999999999',
        'generate_code' => 'Generate Pairing Code',
        'code_title' => 'Your Pairing Code',
        'code_instructions' => 'Enter this code in WhatsApp on your phone (Linked Devices > Link with Phone Number).',
        'copy' => 'Copy Code',
        'copied' => 'Copied!',
        'waiting' => 'Waiting for device confirmation...',
    ],
    'widgets' => [
        'total_instances' => 'Total Instances',
        'connected_instances' => 'Connected',
        'connecting_instances' => 'Connecting',
        'disconnected_instances' => 'Disconnected',
    ],
];
