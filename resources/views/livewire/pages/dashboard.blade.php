@if ($isPeserta)
    @include('livewire.pages.dashboard-peserta')
@elseif ($canViewStats)
    @include('livewire.pages.dashboard-stats')
@else
    @include('livewire.pages.dashboard-welcome')
@endif
