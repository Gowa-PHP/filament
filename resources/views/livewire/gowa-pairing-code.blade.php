<div class="p-4 space-y-6"
     @if($status === 'connecting') wire:poll.3s="checkStatus" @endif>

    @if($status === 'connected')
        <div class="flex flex-col items-center space-y-2 text-emerald-600 dark:text-emerald-400 text-center py-4">
            <svg class="w-16 h-16 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h3 class="text-xl font-bold">
                {{ __('gowa-filament::gowa-filament.qr.connected') }}
            </h3>
        </div>
    @elseif($pairingCode)
        <div class="flex flex-col items-center justify-center space-y-4 text-center">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                {{ __('gowa-filament::gowa-filament.pairing.code_title') }}
            </h4>

            <div x-data="{ copied: false }" class="flex items-center justify-center space-x-3">
                <div class="px-6 py-3 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl font-mono text-3xl font-bold tracking-widest text-primary-600 dark:text-primary-400 shadow-inner select-all">
                    {{ strlen($pairingCode) === 8 ? substr($pairingCode, 0, 4) . '-' . substr($pairingCode, 4) : $pairingCode }}
                </div>
                <button type="button"
                        x-on:click="navigator.clipboard.writeText('{{ $pairingCode }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="p-3 bg-primary-600 hover:bg-primary-500 text-white rounded-xl shadow transition">
                    <template x-if="!copied">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </template>
                    <template x-if="copied">
                        <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </template>
                </button>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                {{ __('gowa-filament::gowa-filament.pairing.code_instructions') }}
            </p>

            <div class="flex items-center space-x-2 text-xs text-amber-600 dark:text-amber-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('gowa-filament::gowa-filament.pairing.waiting') }}</span>
            </div>
        </div>
    @else
        <form wire:submit.prevent="generateCode" class="space-y-4">
            @if($errorMessage)
                <div class="p-3 rounded-lg bg-danger-50 dark:bg-danger-950 text-danger-600 dark:text-danger-400 text-xs">
                    {{ $errorMessage }}
                </div>
            @endif

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('gowa-filament::gowa-filament.pairing.phone_label') }}
                </label>
                <input type="text"
                       id="phone"
                       wire:model.defer="phone"
                       placeholder="{{ __('gowa-filament::gowa-filament.pairing.phone_placeholder') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                @error('phone')
                    <span class="text-xs text-danger-600 dark:text-danger-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white font-medium text-sm rounded-lg shadow inline-flex items-center space-x-2">
                    <span wire:loading.remove>{{ __('gowa-filament::gowa-filament.pairing.generate_code') }}</span>
                    <span wire:loading class="flex items-center space-x-1">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Gerando...</span>
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
