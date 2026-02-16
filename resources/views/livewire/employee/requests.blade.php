<div x-data="{ 
    openMenu: false, 
    showDrawer: false, 
    formType: '' 
}" @request-submitted.window="showDrawer = false" class="max-w-xl mx-auto space-y-6 pb-20 relative">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Requests</h1>
        <div class="text-sm text-gray-500">History</div>
    </div>

    <!-- Requests List -->
    <div class="space-y-4">
        @forelse($requests as $request)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full {{ $request->type_label == 'Leave' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }} flex items-center justify-center font-bold text-xs ring-4 ring-white">
                        {{ substr($request->type_label, 0, 1) }}
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $request->type_label }} Request
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($request->sort_date)->format('d M Y') }}
                            @if($request->type_label == 'Leave')
                                ({{ $request->days }} days)
                            @else
                                ({{ $request->hours }} hrs)
                            @endif
                        </p>
                    </div>
                </div>
                <div>
                    @php
                        $statusColor = match($request->status) {
                            'approved' => 'green',
                            'rejected' => 'red',
                            default => 'yellow',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md bg-{{ $statusColor }}-50 px-2 py-1 text-xs font-medium text-{{ $statusColor }}-700 ring-1 ring-inset ring-{{ $statusColor }}-600/20 capitalize">
                        {{ $request->status }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center py-10">
                <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900">No requests yet</h3>
                <p class="text-sm text-gray-500 mt-1">Create a new request to get started.</p>
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
        <div class="absolute inset-0 bg-gray-500/75 transition-opacity" @click="showDrawer = false"></div>

        <!-- Panel -->
        <div class="relative bg-white rounded-t-3xl shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-4 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-gray-900" x-text="formType === 'leave' ? 'New Leave Request' : 'New Overtime Request'"></h3>
                <button @click="showDrawer = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="px-4 py-3 pb-10 overflow-y-auto">
                <template x-if="formType === 'leave'">
                    @livewire('leave.request-form')
                </template>
                <template x-if="formType === 'overtime'">
                    @livewire('overtime.request-form')
                </template>
            </div>
        </div>
    </div>

    <!-- FAB (Floating Action Button) -->
    <div class="fixed bottom-20 right-4 sm:right-8 z-40">
        <!-- Menu Options -->
        <div x-show="openMenu" @click.away="openMenu = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95" class="absolute bottom-16 right-0 space-y-2 mb-2 min-w-[160px]" style="display: none;">
            
            <button @click="showDrawer = true; formType = 'leave'; openMenu = false" class="flex items-center justify-end group w-full text-right focus:outline-none">
                <span class="mr-2 px-2 py-1 bg-white text-gray-700 text-xs font-medium rounded shadow-sm border border-gray-100">Leave</span>
                <div class="h-10 w-10 rounded-full bg-blue-600 text-white shadow-lg flex items-center justify-center hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </button>

            <button @click="showDrawer = true; formType = 'overtime'; openMenu = false" class="flex items-center justify-end group w-full text-right focus:outline-none">
                <span class="mr-2 px-2 py-1 bg-white text-gray-700 text-xs font-medium rounded shadow-sm border border-gray-100">Overtime</span>
                <div class="h-10 w-10 rounded-full bg-purple-600 text-white shadow-lg flex items-center justify-center hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </button>
        </div>

        <!-- Main FAB -->
        <button @click="openMenu = !openMenu" class="h-14 w-14 rounded-full bg-indigo-600 text-white shadow-xl flex items-center justify-center hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105 active:scale-95">
            <svg x-show="!openMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <svg x-show="openMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>
