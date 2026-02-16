<div class="max-w-xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ now()->format('l, d M Y') }}</p>
            <h1 class="text-2xl font-bold text-gray-900">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', $user->name)[0] }}!</h1>
        </div>
        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
            {{ substr($user->name, 0, 1) }}
        </div>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <svg class="w-24 h-24 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9v-2h2v2zm0-4H9V7h2v5z"/></svg>
        </div>
        
        <div class="p-6 relative z-10">
            @if($status === 'not_checked_in')
                <div class="flex flex-col items-start">
                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 mb-2">Not Checked In</span>
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Ready to start?</h2>
                    <p class="text-gray-500 text-sm mb-4">You haven't checked in for today yet.</p>
                    <a href="{{ route('attendance') }}" wire:navigate class="w-full inline-flex justify-center items-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                        Check In Now
                    </a>
                </div>
            @elseif($status === 'checked_in')
                <div class="flex flex-col items-start">
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5 animate-pulse"></span>
                        Working
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Have a great day!</h2>
                    <p class="text-gray-500 text-sm mb-4">Checked in at <span class="font-mono font-medium text-gray-900">{{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }}</span></p>
                    <a href="{{ route('attendance') }}" wire:navigate class="w-full inline-flex justify-center items-center rounded-xl bg-white border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-all">
                        Check Out
                    </a>
                </div>
            @else
                <div class="flex flex-col items-start">
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 mb-2">Completed</span>
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">All done for today!</h2>
                    <p class="text-gray-500 text-sm mb-4">You checked out at <span class="font-mono font-medium text-gray-900">{{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i') }}</span>.</p>
                    <button disabled class="w-full inline-flex justify-center items-center rounded-xl bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed">
                        Good Job!
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Leave Balance</p>
            <div class="mt-2 flex items-baseline">
                <span class="text-2xl font-bold text-gray-900">{{ number_format($leaveBalance, 0) }}</span>
                <span class="ml-1 text-sm text-gray-500">days</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Days Worked</p>
            <div class="mt-2 flex items-baseline">
                <!-- Placeholder for Days Worked in Month -->
                <span class="text-2xl font-bold text-gray-900">{{ \App\Models\Attendance::where('user_id', $user->id)->whereMonth('date', now()->month)->count() }}</span>
                <span class="ml-1 text-sm text-gray-500">this mo.</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-semibold text-gray-900">Recent Activity</h3>
            <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">View All</a>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            @forelse($recentActivity as $log)
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full {{ $log->is_late ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600' }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($log->date)->format('D, d M') }}</p>
                            <p class="text-xs text-gray-500">
                                In: {{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                @if($log->check_out_time) • Out: {{ \Carbon\Carbon::parse($log->check_out_time)->format('H:i') }} @endif
                            </p>
                        </div>
                    </div>
                    <div>
                        @if($log->is_late)
                            <span class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">Late</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">On Time</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500 text-sm">
                    No recent activity found.
                </div>
            @endforelse
        </div>
    </div>
</div>
