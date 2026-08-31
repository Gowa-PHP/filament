<x-filament-panels::page>
    <form wire:submit.prevent="send" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800"
             style="display: flex; items-center: center; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid rgba(229, 231, 235, 0.2);">

            <x-filament::button type="submit" icon="heroicon-o-paper-airplane" color="success">
                {{ __('gowa-filament::gowa-filament.actions.submit_send') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
