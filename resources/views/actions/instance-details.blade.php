@php
    $deviceId = $record->device_id ?? (string) $record->getKey();
    $phone = $record->phone_number;
    $webhookUrl = url(config('gowa.webhook.path', 'webhooks/gowa') . '/' . $deviceId);
    
    $avatarUrl = null;
    $officialName = null;
    $jid = null;

    if (! empty($deviceId)) {
        $avatarUrl = cache()->remember('gowa_avatar_' . $deviceId, 86400, function () use ($deviceId, $phone) {
            try {
                return $phone ? \Gowa\Laravel\Facades\Gowa::avatar($deviceId, $phone)?->url : null;
            } catch (\Throwable $e) {
                return null;
            }
        });

        $deviceInfo = cache()->remember('gowa_device_info_' . $deviceId, 3600, function () use ($deviceId) {
            try {
                return \Gowa\Laravel\Facades\Gowa::device($deviceId);
            } catch (\Throwable $e) {
                return null;
            }
        });

        $officialName = $deviceInfo?->name;
        $jid = $deviceInfo?->jid;
    }

    $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'WA') . '&color=128C7E&background=DCF8C6&size=128';
    $statusValue = $record->status instanceof \Gowa\Laravel\Enums\GowaInstanceStatus ? $record->status->value : (string) $record->status;
    $isConnected = in_array($statusValue, ['open', 'connected'], true);
@endphp

<div class="space-y-6 p-2 text-sm text-gray-700 dark:text-gray-200">
    <!-- Header with Large Profile Avatar & Title -->
    <div class="flex flex-col items-center justify-center text-center space-y-3 pb-4 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
            <img src="{{ $avatarUrl ?? $defaultAvatar }}" alt="{{ $record->name }}" class="w-24 h-24 rounded-full object-cover shadow-md ring-4 ring-emerald-500/20" />
            <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full border-2 border-white dark:border-gray-800 {{ $isConnected ? 'bg-emerald-500' : 'bg-rose-500' }}" title="{{ ucfirst($statusValue) }}"></span>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $record->name }}</h3>
            @if(! empty($officialName))
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-0.5">Perfil WhatsApp: {{ $officialName }}</p>
            @endif
            @if(! empty($phone))
                <p class="text-sm font-mono text-gray-500 dark:text-gray-400 mt-1">+{{ $phone }}</p>
            @endif
        </div>
    </div>

    <!-- Connected Device Information Grid -->
    <div class="grid grid-cols-1 gap-4">
        <!-- Status -->
        <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg flex items-center justify-between">
            <span class="font-medium text-gray-600 dark:text-gray-400">Status da Conexão</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isConnected ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                {{ $isConnected ? __('gowa-filament::gowa-filament.status.connected') : __('gowa-filament::gowa-filament.status.disconnected') }}
            </span>
        </div>

        <!-- JID WhatsApp -->
        @if(! empty($jid))
            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg space-y-1">
                <span class="font-medium text-xs text-gray-500 dark:text-gray-400">JID WhatsApp</span>
                <p class="font-mono text-xs text-gray-800 dark:text-gray-200 select-all">{{ $jid }}</p>
            </div>
        @endif

        <!-- Device ID -->
        <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg space-y-1">
            <span class="font-medium text-xs text-gray-500 dark:text-gray-400">ID do Dispositivo (Device ID)</span>
            <div class="flex items-center justify-between">
                <p class="font-mono text-xs text-gray-800 dark:text-gray-200 select-all">{{ $deviceId }}</p>
            </div>
        </div>

        <!-- Webhook URL -->
        <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg space-y-1">
            <span class="font-medium text-xs text-gray-500 dark:text-gray-400">URL do Webhook</span>
            <div class="flex items-center justify-between gap-2">
                <p class="font-mono text-xs text-gray-800 dark:text-gray-200 break-all select-all">{{ $webhookUrl }}</p>
            </div>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                <span class="block text-gray-500 dark:text-gray-400">Conectado em</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $record->connected_at ? $record->connected_at->format('d/m/Y H:i') : 'N/A' }}</span>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                <span class="block text-gray-500 dark:text-gray-400">Última Atividade</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $record->updated_at ? $record->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>
