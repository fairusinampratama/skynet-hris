<div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 shadow-md sm:hidden">
    <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">
        
        <!-- Home / Dashboard -->
        <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-500' }}">
            <svg class="w-6 h-6 mb-1 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
        <span class="text-xs {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-bold' : 'text-gray-500 group-hover:text-gray-700' }}">{{ __('Home') }}</span>
        </a>

        <!-- Attendance -->
        <a href="{{ route('attendance') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group {{ request()->routeIs('attendance') ? 'text-indigo-600' : 'text-gray-500' }}">
            <svg class="w-6 h-6 mb-1 {{ request()->routeIs('attendance') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
        <span class="text-xs {{ request()->routeIs('attendance') ? 'text-indigo-600 font-bold' : 'text-gray-500 group-hover:text-gray-700' }}">{{ __('Attend') }}</span>
        </a>

        <!-- Requests -->
        <a href="{{ route('requests') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group {{ request()->routeIs('requests') ? 'text-indigo-600' : 'text-gray-500' }}">
             <svg class="w-6 h-6 mb-1 {{ request()->routeIs('requests') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
        <span class="text-xs {{ request()->routeIs('requests') ? 'text-indigo-600 font-bold' : 'text-gray-500 group-hover:text-gray-700' }}">{{ __('Requests') }}</span>
        </a>

        <!-- Profile -->
        <a href="{{ route('profile') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group {{ request()->routeIs('profile') ? 'text-indigo-600' : 'text-gray-500' }}">
             <div class="w-6 h-6 mb-1 rounded-full {{ request()->routeIs('profile') ? 'bg-indigo-100 text-indigo-700 ring-2 ring-indigo-50' : 'bg-gray-200 text-gray-600 group-hover:bg-gray-300' }} flex items-center justify-center font-bold text-xs transition-all">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
        <span class="text-xs {{ request()->routeIs('profile') ? 'text-indigo-600 font-bold' : 'text-gray-500 group-hover:text-gray-700' }}">{{ __('Profile') }}</span>
        </a>
    </div>
</div>
