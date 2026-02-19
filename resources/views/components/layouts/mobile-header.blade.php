@props(['title' => 'Skynet HRIS', 'showAvatar' => true])

<!-- Fixed Top App Bar -->
<div class="fixed top-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-100 px-4 h-16 flex items-center justify-between sm:hidden">
    <div class="flex items-center gap-3">
        <!-- Optional Back Button or Icon could go here -->
        <div>
            <p class="text-[10px] text-gray-500 font-medium leading-tight">{{ now()->isoFormat('ddd, D MMM') }}</p>
            <h1 class="text-lg font-bold text-gray-900 leading-tight tracking-tight">{{ __($title) }}</h1>
        </div>
    </div>
    
    <!-- Right Actions -->
    <div class="flex items-center gap-2">
        @if($showAvatar)
            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs ring-2 ring-white shadow-sm">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
        @endif
    </div>
</div>

<!-- Spacer to prevent content overlap -->
<div class="h-16 w-full sm:hidden"></div>
