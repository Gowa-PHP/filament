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
    public int $expiresAtTimestamp = 0;
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
        $this->expiresAtTimestamp = time() + 45;

        try {
            $pairing = Gowa::startQrPairing($this->deviceId);

            if ($pairing) {
                $qr = $pairing->qrLink ?? (is_array($pairing->raw ?? null) ? ($pairing->raw['qr_link'] ?? $pairing->raw['qr'] ?? $pairing->raw['code'] ?? null) : null);

                if (! empty($qr)) {
                    if (str_starts_with($qr, 'data:image') || str_starts_with($qr, 'http://') || str_starts_with($qr, 'https://')) {
                        $this->qrCodeUrl = $qr;
                    } elseif (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $qr) && strlen($qr) > 100) {
                        $this->qrCodeUrl = 'data:image/png;base64,' . $qr;
                    } else {
                        $this->qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' . urlencode($qr);
                    }
                } else {
                    $this->qrCodeUrl = null;
                }
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
        if ($this->status === 'connected') {
            return;
        }

        if ($this->isExpired) {
            return;
        }

        if ($this->expiresAtTimestamp > 0 && time() >= $this->expiresAtTimestamp) {
            $this->isExpired = true;
            $this->qrCodeUrl = null;

            return;
        }

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
