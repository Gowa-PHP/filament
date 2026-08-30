<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation Settings
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'group' => 'WhatsApp',
        'sort' => 1,
        'icon' => 'heroicon-o-chat-bubble-left-right',
        'should_register_navigation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Instance Model
    |--------------------------------------------------------------------------
    |
    | Model used to interact with GOWA devices/instances.
    | Defaults to \Gowa\Laravel\Models\GowaInstance::class.
    |
    */
    'model' => \Gowa\Laravel\Models\GowaInstance::class,

    /*
    |--------------------------------------------------------------------------
    | Livewire Polling Intervals (in seconds)
    |--------------------------------------------------------------------------
    */
    'polling' => [
        'qr_code_interval' => 3,
        'pairing_code_interval' => 3,
    ],
];
