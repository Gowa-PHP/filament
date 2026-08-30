<?php

namespace Gowa\Filament\Livewire;

use Exception;
use Gowa\Laravel\Facades\Gowa;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class GowaQrCode extends Component
{
    public string $deviceId;
    public ?string $qrCodeUrl = null;
    public string $status = 'connecting';
    public bool $isExpired = false;
    public ?string $errorMessage = null;

    public function getErrorBag(): MessageBagContract
    {
        $bag = \Livewire\store($this)->get('errorBag');

        if (! $bag instanceof MessageBagContract) {
            $bag = new MessageBag();
            \Livewire\store($this)->set('errorBag', $bag);
        }

        return $bag;
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
        $this->loadQrCode();
    }

    public function loadQrCode(): void
    {
        $this->errorMessage = null;
        $this->isExpired = false;

        try {
            $pairing = Gowa::startQrPairing($this->deviceId);

            if ($pairing && $pairing->qrLink) {
                $this->qrCodeUrl = $pairing->qrLink;
            } else {
                $this->qrCodeUrl = null;
            }

            $this->checkStatus();
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function checkStatus(): void
    {
        try {
            $device = Gowa::device($this->deviceId);

            if ($device) {
                $this->status = $device->status ?? 'connecting';

                if ($device->isPaired() || $this->status === 'open' || $this->status === 'connected') {
                    $this->status = 'connected';
                    $this->dispatch('gowa-device-connected', deviceId: $this->deviceId);
                }
            }
        } catch (Exception $e) {
            // Keep current status if check fails silently during polling
        }
    }

    public function refreshQrCode(): void
    {
        $this->loadQrCode();
    }

    public function render()
    {
        return view('gowa-filament::livewire.gowa-qr-code');
    }
}
