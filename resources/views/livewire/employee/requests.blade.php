<div x-data="{
    showDrawer: false
}" @request-submitted.window="showDrawer = false" class="max-w-xl mx-auto pb-24 relative">
    <x-layouts.mobile-header title="{{ __('Pengajuan Izin') }}" />

    <!-- Header (Desktop) -->
    <div class="hidden sm:flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            <h1 class="text-2xl font-bold text-gray-900 mt-0.5">{{ __('Pengajuan Izin') }}</h1>
        </div>
        <div class="h-11 w-11 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-base">
            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
    </div>

    <!-- Requests List -->
    <div class="space-y-3 mt-2">
        @forelse($requests as $request)
            @php
                // Unique color + abbreviation per type
                $typeConfig = match($request->type) {
                    'Izin Sakit'             => ['bg' => '#FEF9C3', 'text' => '#854D0E', 'abbr' => 'SK', 'dot' => '#EAB308'],
                    'Izin Telat'             => ['bg' => '#DBEAFE', 'text' => '#1E40AF', 'abbr' => 'TL', 'dot' => '#3B82F6'],
                    'Izin Keperluan Pribadi' => ['bg' => '#F3E8FF', 'text' => '#6B21A8', 'abbr' => 'PR', 'dot' => '#A855F7'],
                    default                  => ['bg' => '#F3F4F6', 'text' => '#374151', 'abbr' => '??', 'dot' => '#9CA3AF'],
                };
                $statusConfig = match($request->status) {
                    'approved' => ['label' => 'Disetujui', 'bg' => '#F0FDF4', 'text' => '#15803D', 'border' => '#86EFAC'],
                    'rejected' => ['label' => 'Ditolak',   'bg' => '#FFF1F2', 'text' => '#BE123C', 'border' => '#FCA5A5'],
                    default    => ['label' => 'Menunggu',  'bg' => '#FFFBEB', 'text' => '#92400E', 'border' => '#FCD34D'],
                };
                $singleDay = ($request->start_date == $request->end_date);
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3.5 flex items-center gap-3 min-h-[68px]">
                {{-- Type Avatar --}}
                <div class="shrink-0 h-11 w-11 rounded-xl flex items-center justify-center font-bold text-xs"
                     style="background-color: {{ $typeConfig['bg'] }}; color: {{ $typeConfig['text'] }};">
                    {{ $typeConfig['abbr'] }}
                </div>

                {{-- Main Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $request->type }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($singleDay)
                            {{ \Carbon\Carbon::parse($request->start_date)->isoFormat('D MMM Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($request->start_date)->isoFormat('D MMM') }} — {{ \Carbon\Carbon::parse($request->end_date)->isoFormat('D MMM Y') }}
                        @endif
                        · {{ $request->days }} hari
                    </p>
                </div>

                {{-- Status Badge --}}
                <div class="shrink-0">
                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold border"
                          style="background-color: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['text'] }}; border-color: {{ $statusConfig['border'] }};">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="h-20 w-20 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 text-gray-300 border border-gray-100">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">{{ __('Belum ada pengajuan') }}</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-[200px]">{{ __('Tekan tombol + di bawah untuk membuat pengajuan izin baru.') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Slide-over Drawer -->
    <div x-show="showDrawer"
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed inset-0 z-50 flex flex-col justify-end" style="display: none;">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showDrawer = false"></div>

        <!-- Panel -->
        <div class="relative bg-white rounded-t-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <!-- Handle bar -->
            <div class="flex justify-center pt-3 pb-1">
                <div class="h-1 w-10 rounded-full bg-gray-200"></div>
            </div>
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">{{ __('Pengajuan Izin Baru') }}</h3>
                <button @click="showDrawer = false" class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="px-5 py-4 pb-10 overflow-y-auto">
                @livewire('leave.request-form')
            </div>
        </div>
    </div>

    <!-- FAB -->
    <div class="fixed bottom-20 right-4 sm:right-8 z-40">
        <button @click="showDrawer = true"
                class="h-14 w-14 rounded-full bg-indigo-600 text-white shadow-xl flex items-center justify-center hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105 active:scale-95">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>
</div>
