<div class="space-y-6">

    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Date') }}</label>
                <input type="date" wire:model="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                @error('date') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Hours') }}</label>
                <input type="number" step="0.5" wire:model="hours" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4" placeholder="e.g. 2.5">
                @error('hours') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Reason') }}</label>
            <textarea wire:model="reason" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 h-32 resize-none" placeholder="{{ __('Explain overtime work...') }}"></textarea>
            @error('reason') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:bg-indigo-800 transition-colors">
                {{ __('Submit Request') }}
            </button>
        </div>
    </form>
    
    <!-- History List (Simple Table-like) -->
    <div class="pt-8 space-y-4">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b border-gray-100 pb-2">{{ __('Recent Requests') }}</h2>
        
        <div class="space-y-0">
            @foreach($myRequests as $req)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors">
                    <div>
                        <div class="text-sm font-bold text-gray-900">{{ $req->date->isoFormat('ddd, D MMM') }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $req->hours }} {{ __('Hours') }}</div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $req->status === 'approved' ? 'bg-green-100 text-green-800' : ($req->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($req->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
            
            @if($myRequests->isEmpty())
                <div class="text-center py-8 text-gray-400 text-sm italic">
                    No history found.
                </div>
            @endif
        </div>
    </div>
</div>
