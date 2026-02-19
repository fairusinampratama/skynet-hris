<div class="space-y-6">

    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Balances Section (High Contrast, Simple) -->
    <div class="bg-gray-100 rounded-xl p-4">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">{{ __('My Balances') }}</h2>
        <div class="grid grid-cols-2 gap-4">
            @foreach($balances as $balance)
                <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ $balance->leaveType->name }}</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $balance->remaining_days ?? '∞' }} <span class="text-xs font-normal text-gray-500">{{ __('Days') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <form wire:submit="submit" class="space-y-6">
        
        <!-- Leave Type -->
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Leave Type') }}</label>
            <select wire:model="leave_type_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                <option value="">{{ __('Select Type...') }}</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('leave_type_id') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Start Date') }}</label>
                <input type="date" wire:model="start_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                @error('start_date') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('End Date') }}</label>
                <input type="date" wire:model="end_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                @error('end_date') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Reason -->
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Reason') }}</label>
            <textarea wire:model="reason" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 h-32 resize-none" placeholder="{{ __('Describe your reason for leave...') }}"></textarea>
            @error('reason') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
        </div>

        <!-- Attachment -->
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Attachment') }} <span class="text-gray-400 font-normal text-xs">({{ __('If Required') }})</span></label>
            <div class="flex items-center justify-center w-full">
                <label for="file-upload" class="flex flex-col items-center justify-center w-full h-16 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <span class="text-sm font-medium text-gray-600">
                            @if($attachment)
                                {{ $attachment->getClientOriginalName() }}
                            @else
                                {{ __('Upload File') }}
                            @endif
                        </span>
                    </div>
                </label>
                <input id="file-upload" type="file" wire:model="attachment" class="hidden" />
            </div>
            @error('attachment') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4 pb-8">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:bg-indigo-800 transition-colors">
                {{ __('Submit Request') }}
            </button>
        </div>
    </form>
</div>
