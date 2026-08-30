<div class="flex flex-col items-center justify-center p-6 text-center space-y-5"
     style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; gap: 1.25rem; width: 100%;"
     @if($status !== 'connected') wire:poll.3s="checkStatus" @endif>

    @if($status === 'connected')
        <div class="flex flex-col items-center space-y-3 py-6 text-emerald-600 dark:text-emerald-400"
             style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem; padding-top: 1.5rem; padding-bottom: 1.5rem;">
            <div class="p-3 bg-emerald-100 dark:bg-emerald-950/60 rounded-full"
                 style="padding: 0.75rem; border-radius: 9999px;">
                <svg class="w-12 h-12 text-emerald-600 dark:text-emerald-400" style="width: 3rem; height: 3rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100" style="font-size: 1.125rem; font-weight: 700;">
                {{ __('gowa-filament::gowa-filament.qr.connected') }}
            </h3>
        </div>
    @elseif($errorMessage)
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm max-w-sm text-left space-y-2"
             style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%; max-width: 24rem; padding: 1rem; border-radius: 0.75rem; text-align: left;">
            <p class="font-semibold flex items-center gap-1.5 text-red-800 dark:text-red-200" style="display: flex; align-items: center; gap: 0.375rem; font-weight: 600;">
                <svg class="w-4 h-4 text-red-500 shrink-0" style="width: 1rem; height: 1rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('gowa-filament::gowa-filament.notifications.error_occurred') }}
            </p>
            <p class="text-xs font-mono break-all opacity-90 leading-relaxed" style="font-size: 0.75rem; word-break: break-all; font-family: monospace;">{{ $errorMessage }}</p>
            <div class="pt-2 flex justify-end" style="display: flex; justify-content: flex-end; pt: 0.5rem;">
                <button type="button" wire:click="refreshQrCode" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
                        style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 0.5rem; background-color: #dc2626; color: #ffffff; cursor: pointer; border: none;">
                    <svg class="w-3.5 h-3.5" style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    {{ __('gowa-filament::gowa-filament.qr.refresh') }}
                </button>
            </div>
        </div>
    @elseif($qrCodeUrl)
        <div class="flex flex-col items-center space-y-4"
             style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; width: 100%;">
            <div class="p-3 bg-white rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 inline-block"
                 style="padding: 0.75rem; background-color: #ffffff; border-radius: 1rem; border: 1px solid #e5e7eb; display: inline-block;">
                <img src="{{ $qrCodeUrl }}" alt="WhatsApp QR Code" class="w-56 h-56 object-contain rounded-xl"
                     style="width: 200px; height: 200px; max-width: 100%; object-fit: contain; border-radius: 0.75rem; display: block;" />
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed"
               style="font-size: 0.75rem; max-width: 20rem; text-align: center; margin: 0;">
                {{ __('gowa-filament::gowa-filament.qr.instructions') }}
            </p>

            <div class="flex items-center justify-center space-x-2 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-full border border-amber-200 dark:border-amber-900/50"
                 style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 9999px;">
                <svg class="w-3.5 h-3.5 animate-spin" style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('gowa-filament::gowa-filament.qr.waiting') }}</span>
            </div>

            <div style="display: flex; justify-content: center;">
                <button type="button" wire:click="refreshQrCode" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition border border-gray-200 dark:border-gray-700"
                        style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 0.5rem; cursor: pointer;">
                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    {{ __('gowa-filament::gowa-filament.qr.refresh') }}
                </button>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center space-y-3 py-8"
             style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; padding-top: 2rem; padding-bottom: 2rem;">
            <svg class="w-8 h-8 animate-spin text-primary-600 dark:text-primary-400" style="width: 2rem; height: 2rem;" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-xs text-gray-500 dark:text-gray-400" style="font-size: 0.75rem;">{{ __('gowa-filament::gowa-filament.qr.waiting') }}</p>
        </div>
    @endif
</div>
