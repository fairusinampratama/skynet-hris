<x-filament-panels::page>
    @php
        $data = $this->employeesAndSchedules;
        $employees = $data['employees'];
        $scheduleMap = $data['map'];
        $holidays = $data['holidays'] ?? [];
    @endphp

    <div style="height: calc(100vh - 11rem); overflow: hidden;" class="flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-white/10 flex-shrink-0">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select wire:model.live="month" class="w-full sm:w-40 appearance-none bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5 pr-8">
                    @foreach(range(1, 12) as $m)<option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>@endforeach
                </select>
                <select wire:model.live="year" class="w-32 appearance-none bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5 pr-8">
                    @foreach(range(now()->year - 1, now()->year + 1) as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                </select>
            </div>
            @if(count($employees) > 0)
            <div class="flex flex-wrap items-center gap-6 text-xs font-medium bg-gray-50 dark:bg-white/5 px-4 py-3 rounded-lg border border-gray-200 dark:border-white/5 shadow-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <div class="w-6 h-6 flex items-center justify-center rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"><div class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></div></div>
                    <span>Default</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <div class="w-6 h-6 flex items-center justify-center rounded bg-red-100 dark:bg-red-900/30"><x-heroicon-m-x-mark class="w-4 h-4 text-red-600 dark:text-red-400" /></div>
                    <span>OFF</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <div class="w-6 h-6 flex items-center justify-center rounded bg-green-100 dark:bg-green-900/30"><x-heroicon-m-check class="w-4 h-4 text-green-600 dark:text-green-400" /></div>
                    <span>ON</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 pl-4 border-l border-gray-200 dark:border-gray-700">
                    <div class="w-6 h-6 flex items-center justify-center rounded bg-blue-100 dark:bg-blue-900/30 ring-1 ring-blue-200 dark:ring-blue-700"><span class="text-[10px] font-bold text-blue-700 dark:text-blue-300">{{ now()->day }}</span></div>
                    <span>Today</span>
                </div>
            </div>
            @endif
        </div>

        <div class="flex-1 min-h-0 overflow-auto bg-white rounded-lg shadow ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @if(count($employees) > 0)
            <table class="w-full text-xs text-left border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 sticky top-0 left-0 z-30 bg-gray-100 dark:bg-gray-800 border-r border-b border-gray-200 dark:border-gray-700 min-w-[200px]">Employee</th>
                        @foreach($this->days as $date)
                            @php
                                $isToday = $date->isToday(); $isSunday = $date->dayOfWeek === \Carbon\Carbon::SUNDAY;
                                $holiday = $holidays[$date->day] ?? null; $isHoliday = $holiday !== null;
                                $holidayName = $isHoliday ? $holiday->name : ($isSunday ? 'Sunday' : '');
                                if ($isToday) { $hStyle = 'background-color:#dbeafe;color:#1d4ed8;'; }
                                elseif ($isHoliday) { $hStyle = 'background-color:#fecaca;color:#b91c1c;'; }
                                elseif ($isSunday) { $hStyle = 'background-color:#fee2e2;color:#dc2626;'; }
                                else { $hStyle = 'background-color:#f3f4f6;color:#374151;'; }
                            @endphp
                            <th class="px-1 py-2 text-center min-w-[44px] border-b border-r border-gray-200 dark:border-gray-700 sticky top-0 z-20" style="{{ $hStyle }}" title="{{ $holidayName }}">
                                <div class="font-bold {{ $isToday ? 'text-sm' : '' }}">{{ $date->day }}</div>
                                <div class="text-[9px] uppercase tracking-wider opacity-80">{{ $date->format('D') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-4 py-3 font-medium whitespace-nowrap sticky left-0 z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-white/5 border-r border-b border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-900 dark:text-white font-semibold">{{ $employee->user->name }}</div>
                                <div class="text-[10px] text-gray-500">{{ $employee->department->name }}</div>
                            </td>
                            @foreach($this->days as $date)
                                @php
                                    $day = $date->day; $schedule = $scheduleMap[$employee->id][$day] ?? null;
                                    $isSunday = $date->dayOfWeek === \Carbon\Carbon::SUNDAY; $isToday = $date->isToday();
                                    $holiday = $holidays[$day] ?? null; $isHoliday = $holiday !== null;
                                    if ($schedule) {
                                        if ($schedule->is_off) { $icon = 'heroicon-m-x-mark'; $color = 'text-red-600 dark:text-red-400'; $cellStyle = 'background-color:#fee2e2;'; $tooltip = 'OFF'; }
                                        else { $icon = 'heroicon-m-check'; $color = 'text-green-600 dark:text-green-400'; $cellStyle = 'background-color:#dcfce7;'; $tooltip = 'ON (Custom)'; }
                                    } else {
                                        $icon = ''; $color = 'text-gray-400';
                                        if ($isHoliday) { $cellStyle = 'background-color:#fecaca;'; $tooltip = 'Holiday: ' . $holiday->name; }
                                        elseif ($isSunday) { $cellStyle = 'background-color:#fee2e2;'; $tooltip = 'Sunday (OFF)'; }
                                        else { $cellStyle = 'background-color:#ffffff;'; $tooltip = 'Default (08:00 - 17:00)'; }
                                    }
                                    $ring = $isToday ? 'ring-1 ring-inset ring-blue-200 dark:ring-blue-700' : '';
                                @endphp
                                <td wire:key="cell-{{ $employee->id }}-{{ $date->format('Y-m-d') }}" wire:click="toggleDay({{ $employee->id }}, '{{ $date->format('Y-m-d') }}')"
                                    class="px-1 py-1 text-center border-r border-b border-gray-100 dark:border-gray-800 cursor-pointer transition-all duration-75 select-none {{ $ring }} hover:brightness-95 dark:hover:brightness-110 h-12" style="{{ $cellStyle }}" title="{{ $tooltip }}">
                                    <div class="flex items-center justify-center w-full h-full" wire:loading.class="opacity-50" wire:target="toggleDay({{ $employee->id }}, '{{ $date->format('Y-m-d') }}')">
                                        @if($icon)<x-icon name="{{ $icon }}" class="w-5 h-5 {{ $color }} font-bold" />
                                        @elseif(!$isHoliday && !$isSunday)<div class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></div>@endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                <div class="w-16 h-16 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mb-4">
                    <x-heroicon-o-calendar-days class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ __('No Shift Schedules Found') }}
                </h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 mb-6 max-w-sm">
                    {{ __('This calendar only shows employees from departments with') }} 
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Shift Schedule') }}</span> {{ __('enabled.') }}
                </p>
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Resources\DepartmentResource::getUrl() }}"
                    color="gray"
                    size="sm"
                >
                    {{ __('Configure Departments') }}
                </x-filament::button>
            </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
