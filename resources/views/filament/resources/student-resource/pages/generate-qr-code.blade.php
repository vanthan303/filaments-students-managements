<x-filament-panels::page>
    {!! QrCode::size(200)->generate($this->getRecord()->name) !!}
</x-filament-panels::page>
