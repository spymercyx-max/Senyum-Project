<?php

namespace App\Livewire\Distributor;

use App\Services\MapService;
use Livewire\Component;

class OutletLocationPicker extends Component
{
    public string $url = '';

    public $lat = null;

    public $lng = null;

    public string $status = 'idle';

    public string $message = '';

    public function mount($value = ''): void
    {
        $this->url = is_string($value) ? trim($value) : (string) $value;
        $this->lat = null;
        $this->lng = null;
        $this->status = 'idle';
        $this->message = '';

        if ($this->url !== '') {
            $this->lookup();
        }
    }

    public function updatedUrl(): void
    {
        $this->url = trim((string) $this->url);

        if ($this->url === '') {
            $this->lat = null;
            $this->lng = null;
            $this->status = 'idle';
            $this->message = '';

            return;
        }

        $this->lookup();
    }

    protected function lookup(): void
    {
        try {
            /** @var MapService $maps */
            $maps = app(MapService::class);
            $coords = $maps->coordinatesFromLink($this->url);
            $this->lat = $coords['lat'];
            $this->lng = $coords['lng'];
            $this->status = 'ok';
            $this->message = 'LOKASI TERBACA ✓';
        } catch (\Throwable $e) {
            $this->lat = null;
            $this->lng = null;
            $this->status = 'error';
            $this->message = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.distributor.outlet-location-picker');
    }
}
