<?php

namespace Gowa\Filament\Livewire;

use Exception;
use Gowa\Laravel\Facades\Gowa;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class GowaPairingCode extends Component
{
    public string $deviceId;
    public string $phone = '';
    public ?string $pairingCode = null;
    public string $status = 'disconnected';
    public ?string $errorMessage = null;

    public function getErrorBag(): MessageBagContract
    {
        $stored = \Livewire\store($this)->get('errorBag');
        if ($stored instanceof MessageBagContract && $stored->isNotEmpty()) {
            return $stored;
        }

        return $this->errorBag ??= new MessageBag();
    }

    public function setErrorBag($bag): MessageBagContract
    {
        $bagInstance = match (true) {
            $bag instanceof MessageBagContract => $bag,
            $bag instanceof Arrayable => new MessageBag($bag->toArray()),
            is_array($bag) => new MessageBag($bag),
            default => new MessageBag(),
        };

        \Livewire\store($this)->set('errorBag', $bagInstance);

        return $bagInstance;
    }

    public function mount(string $deviceId): void
    {
        $this->deviceId = $deviceId;
        $this->checkStatus();
    }

    public function generateCode(): void
    {
        $this->errorMessage = null;

        try {
            $this->validate([
                'phone' => ['required', 'string', 'min:8', 'max:20'],
            ]);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->errorMessage = $e->validator->errors()->first('phone');
            return;
        }

        try {
            $cleanPhone = preg_replace('/\D/', '', $this->phone);
            $pairing = Gowa::startCodePairing($this->deviceId, $cleanPhone);

            if ($pairing && $pairing->pairCode) {
                $this->pairingCode = $pairing->pairCode;
                $this->status = 'connecting';
            } else {
                $this->errorMessage = 'Could not generate pairing code. Please try again.';
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function checkStatus(): void
    {
        try {
            $device = Gowa::device($this->deviceId);

            if ($device) {
                $this->status = $device->status ?? 'disconnected';

                if ($device->isPaired() || $this->status === 'open' || $this->status === 'connected') {
                    $this->status = 'connected';
                    $this->dispatch('gowa-device-connected', deviceId: $this->deviceId);
                }
            }
        } catch (Exception $e) {
            // Keep current status
        }
    }

    public function render()
    {
        return view('gowa-filament::livewire.gowa-pairing-code');
    }
}
