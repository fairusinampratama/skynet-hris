<x-filament-panels::page>
    <div style="height: calc(100vh - 11rem); overflow: hidden;" class="flex flex-col gap-4">

        {{-- Controls Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-white/10 flex-shrink-0">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select wire:model.live="month" class="w-full sm:w-40 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="year" class="w-32 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                    @foreach(range(now()->year - 1, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                <select wire:model.live="departmentFilter" class="w-full sm:w-48 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                    <option value="">All Departments</option>
                    @foreach($this->departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs font-medium bg-gray-50 dark:bg-white/5 px-4 py-2.5 rounded-lg border border-gray-200 dark:border-white/5">
                <div class="flex items-center gap-1.5">
                    <div class="w-5 h-5 flex items-center justify-center rounded bg-green-100 dark:bg-green-900/30"><x-heroicon-m-check class="w-3.5 h-3.5 text-green-600" /></div>
                    <span class="text-gray-600 dark:text-gray-400">On Time</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-5 h-5 flex items-center justify-center rounded bg-amber-100 dark:bg-amber-900/30"><x-heroicon-m-clock class="w-3.5 h-3.5 text-amber-600" /></div>
                    <span class="text-gray-600 dark:text-gray-400">Late</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-5 h-5 flex items-center justify-center rounded bg-red-100 dark:bg-red-900/30"><x-heroicon-m-x-mark class="w-3.5 h-3.5 text-red-600" /></div>
                    <span class="text-gray-600 dark:text-gray-400">Absent</span>
                </div>
                <div class="flex items-center gap-1.5 pl-3 border-l border-gray-200 dark:border-gray-700">
                    <div class="w-5 h-5 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700"><span class="text-[9px] text-gray-400">—</span></div>
                    <span class="text-gray-600 dark:text-gray-400">Off / Holiday</span>
                </div>
            </div>
        </div>

        {{-- Scrollable Table --}}
        <div class="flex-1 min-h-0 overflow-auto bg-white rounded-lg shadow ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @php
                $calData = $this->calendarData;
                $employees = $calData['employees'];
                $attendanceMap = $calData['attendanceMap'];
                $holidays = $calData['holidays'] ?? [];
                $today = now()->startOfDay();
            @endphp
            <table class="w-full text-xs text-left border-separate border-spacing-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 sticky top-0 left-0 z-30 bg-gray-100 dark:bg-gray-800 border-r border-b border-gray-200 dark:border-gray-700 min-w-[200px]">
                            Employee
                        </th>
                        @foreach($this->days as $date)
                            @php
                                $isToday = $date->isToday();
                                $isSunday = $date->dayOfWeek === \Carbon\Carbon::SUNDAY;
                                $holiday = $holidays[$date->day] ?? null;
                                $isHoliday = $holiday !== null;
                                $holidayName = $isHoliday ? $holiday->name : ($isSunday ? 'Sunday' : '');
                                // Use inline styles for colors — Tailwind won't compile dynamic PHP strings
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
                        <th class="px-3 py-2 text-center min-w-[100px] border-b border-l-2 border-gray-300 dark:border-gray-600 sticky top-0 right-0 z-30 bg-gray-200 dark:bg-gray-700">
                            <div class="font-bold text-gray-700 dark:text-gray-200">Summary</div>
                            <div class="text-[9px] text-gray-500 dark:text-gray-400">✓ / ⏰ / ✗</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php $presentCount = 0; $lateCount = 0; $absentCount = 0; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-4 py-3 font-medium whitespace-nowrap sticky left-0 z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-white/5 border-r border-b border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-900 dark:text-white font-semibold">{{ $employee->user->name }}</div>
                                <div class="text-[10px] text-gray-500">{{ $employee->department->name }}</div>
                            </td>
                            @foreach($this->days as $date)
                                @php
                                    $day = $date->day;
                                    $isSunday = $date->dayOfWeek === \Carbon\Carbon::SUNDAY;
                                    $isToday = $date->isToday();
                                    $isFuture = $date->startOfDay()->gt($today);
                                    $holiday = $holidays[$day] ?? null;
                                    $isHoliday = $holiday !== null;
                                    $isOffDay = $isSunday || $isHoliday;
                                    $attendance = $attendanceMap[$employee->user_id][$day] ?? null;

                                    if ($isFuture) {
                                        $icon = ''; $color = ''; $cellStyle = 'background-color:#f9fafb;'; $tooltip = $date->format('D, M d'); $status = 'future';
                                    } elseif ($isOffDay && !$attendance) {
                                        // Sunday / Holiday — visible red tint
                                        $icon = ''; $color = ''; $cellStyle = $isHoliday ? 'background-color:#fecaca;' : 'background-color:#fee2e2;'; $tooltip = $isHoliday ? $holiday->name : 'Sunday'; $status = 'off';
                                    } elseif ($attendance) {
                                        if ($attendance->is_late) {
                                            $icon = 'heroicon-m-clock'; $color = 'text-amber-600 dark:text-amber-400'; $cellStyle = 'background-color:#fffbeb;';
                                            $checkIn = $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '?';
                                            $tooltip = "Late — Check-in: {$checkIn}"; $lateCount++; $status = 'late';
                                        } else {
                                            $icon = 'heroicon-m-check'; $color = 'text-green-600 dark:text-green-400'; $cellStyle = 'background-color:#f0fdf4;';
                                            $checkIn = $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '?';
                                            $tooltip = "On Time — Check-in: {$checkIn}"; $presentCount++; $status = 'present';
                                        }
                                    } else {
                                        $icon = 'heroicon-m-x-mark'; $color = 'text-red-500 dark:text-red-400'; $cellStyle = 'background-color:#fef2f2;';
                                        $tooltip = 'Absent'; $absentCount++; $status = 'absent';
                                    }
                                    $ring = $isToday ? 'ring-1 ring-inset ring-blue-300 dark:ring-blue-700' : '';
                                @endphp
                                <td class="px-1 py-1 text-center border-r border-b border-gray-100 dark:border-gray-800 {{ $ring }} h-12" style="{{ $cellStyle }}" title="{{ $tooltip }}">
                                    <div class="flex items-center justify-center w-full h-full">
                                        @if($icon)
                                            <x-icon name="{{ $icon }}" class="w-5 h-5 {{ $color }}" />
                                        @elseif($status === 'off')
                                            <span class="text-[10px] text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            <td class="px-3 py-2 text-center border-b border-l-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 sticky right-0 z-10 text-xs font-semibold whitespace-nowrap">
                                <span class="text-green-600 dark:text-green-400">{{ $presentCount }}</span>
                                <span class="text-gray-300 dark:text-gray-600">/</span>
                                <span class="text-amber-600 dark:text-amber-400">{{ $lateCount }}</span>
                                <span class="text-gray-300 dark:text-gray-600">/</span>
                                <span class="text-red-500 dark:text-red-400">{{ $absentCount }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($this->days) + 2 }}" class="p-12 text-center">
                                <div class="flex flex-col items-center space-y-2">
                                    <x-heroicon-o-users class="w-12 h-12 text-gray-400" />
                                    <span class="text-gray-500 text-lg font-medium">No employees found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
