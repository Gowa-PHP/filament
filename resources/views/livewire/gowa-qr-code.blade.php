<div class="flex flex-col items-center justify-center p-6 text-center space-y-4"
     @if($status !== 'connected') wire:poll.3s="checkStatus" @endif>

    @if($status === 'connected')
        <div class="flex flex-col items-center space-y-2 text-emerald-600 dark:text-emerald-400">
            <svg class="w-16 h-16 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h3 class="text-xl font-bold">
                {{ __('gowa-filament::gowa-filament.qr.connected') }}
            </h3>
        </div>
    @elseif($errorMessage)
        <div class="p-4 rounded-lg bg-danger-50 dark:bg-danger-950 text-danger-600 dark:text-danger-400 text-sm max-w-md">
            <p class="font-semibold">{{ __('gowa-filament::gowa-filament.notifications.error_occurred') }}</p>
            <p class="text-xs mt-1">{{ $errorMessage }}</p>
            <button type="button" wire:click="refreshQrCode" class="mt-3 px-3 py-1 bg-danger-600 text-white text-xs rounded hover:bg-danger-700">
                {{ __('gowa-filament::gowa-filament.qr.refresh') }}
            </button>
        </div>
    @elseif($qrCodeUrl)
        <div class="space-y-3">
            <div class="p-3 bg-white dark:bg-gray-800 rounded-xl shadow-lg inline-block border border-gray-200 dark:border-gray-700">
                <img src="{{ $qrCodeUrl }}" alt="WhatsApp QR Code" class="w-64 h-64 object-contain mx-auto rounded" />
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-sm">
                {{ __('gowa-filament::gowa-filament.qr.instructions') }}
            </p>

            <div class="flex items-center justify-center space-x-2 text-xs text-amber-600 dark:text-amber-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('gowa-filament::gowa-filament.qr.waiting') }}</span>
            </div>

            <div>
                <button type="button" wire:click="refreshQrCode" class="inline-flex items-center text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    {{ __('gowa-filament::gowa-filament.qr.refresh') }}
                </button>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center space-y-3 py-6">
            <svg class="w-8 h-8 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <button type="button" wire:click="refreshQrCode" class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-500">
                {{ __('gowa-filament::gowa-filament.qr.refresh') }}
            </button>
        </div>
    @endif
</div>
