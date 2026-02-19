<div x-data="{ 
    showDrawer: false, 
    formType: '' 
}" 
@profile-updated.window="showDrawer = false" 
@password-updated.window="showDrawer = false" 
class="max-w-xl mx-auto space-y-6 pb-20 relative">
    <x-layouts.mobile-header title="Profile" />

    <!-- Header (Desktop) -->
    <div class="hidden sm:flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ now()->format('l, d M Y') }}</p>
            <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
        </div>
        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
    </div>

    <!-- User Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
        <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-2xl ring-4 ring-indigo-50">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div class="ml-4">
            <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <div class="flex flex-wrap gap-2 mt-2">
                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 capitalize">
                    {{ $user->getRoleNames()->first() ?? 'Employee' }}
                </span>
                @if($user->phone_number)
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">
                        {{ $user->phone_number }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Settings List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-100">
        <button @click="showDrawer = true; formType = 'edit'" class="w-full px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors text-left group">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-700">Edit Profile</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>

        <button @click="showDrawer = true; formType = 'face-enrollment'" class="w-full px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors text-left group">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-100 transition-colors">
                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-700">Register Face ID</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>

        <button @click="showDrawer = true; formType = 'password'" class="w-full px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors text-left group">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-700">Change Password</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
        
        <div class="px-4 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-700">App Version</span>
            </div>
            <span class="text-sm text-gray-500">v2.1.0</span>
        </div>
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
                <h3 class="text-lg font-bold text-gray-900" x-text="
                    formType === 'edit' ? 'Edit Profile' : 
                    formType === 'password' ? 'Change Password' :
                    formType === 'face-enrollment' ? 'Register Face ID' : ''
                "></h3>
                <button @click="showDrawer = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="px-4 py-8 pb-10 overflow-y-auto">
                <template x-if="formType === 'edit'">
                    @livewire('employee.edit-profile')
                </template>
                <template x-if="formType === 'password'">
                    @livewire('employee.change-password')
                </template>
                <template x-if="formType === 'face-enrollment'">
                    @livewire('employee.face-enrollment')
                </template>
            </div>
        </div>
    </div>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full bg-red-50 text-red-600 rounded-xl px-4 py-3 text-sm font-semibold hover:bg-red-100 transition-colors shadow-sm border border-red-100 flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Log Out
        </button>
    </form>
</div>
