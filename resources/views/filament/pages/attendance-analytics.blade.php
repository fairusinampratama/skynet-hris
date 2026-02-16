<x-filament-panels::page>
    @livewire(\App\Filament\Widgets\RealTimeChart::class)

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @livewire(\App\Filament\Widgets\AttendanceTrendChart::class)
        @livewire(\App\Filament\Widgets\DepartmentPunctualityChart::class)
    </div>

    @livewire(\App\Filament\Widgets\LateArrivalsWidget::class)
</x-filament-panels::page>
