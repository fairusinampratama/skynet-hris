<div class="space-y-6">

    <form wire:submit="submit" class="space-y-5">

        <!-- Leave Type -->
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Jenis Izin') }}</label>
            <select wire:model.live="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                <option value="">{{ __('Pilih Jenis Izin...') }}</option>
                @foreach($leaveTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('type') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror

            <!-- Izin Telat hint -->
            @if($type === 'Izin Telat')
                <div class="mt-2 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700 flex gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Izin Telat yang disetujui akan membebaskan potongan keterlambatan pada hari tersebut.</span>
                </div>
            @endif
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Tanggal Mulai') }}</label>
                <input type="date" wire:model="start_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                @error('start_date') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Tanggal Selesai') }}</label>
                <input type="date" wire:model="end_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 px-4">
                @error('end_date') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Reason -->
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">{{ __('Alasan') }}</label>
            <textarea wire:model="reason" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 h-28 resize-none" placeholder="{{ __('Tuliskan alasan izin Anda...') }}"></textarea>
            @error('reason') <p class="mt-1 text-sm text-red-600 font-semibold">{{ $message }}</p> @enderror
        </div>

        <!-- Attachment -->
        <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">
                {{ __('Lampiran') }}
                <span class="text-gray-400 font-normal text-xs">({{ __('Opsional') }})</span>
            </label>
            <div class="flex items-center justify-center w-full">
                <label for="file-upload" class="flex flex-col items-center justify-center w-full h-14 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition-colors">
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

        <div class="pt-2 pb-6">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:bg-indigo-800 transition-colors">
                {{ __('Kirim Permohonan') }}
            </button>
        </div>
    </form>
</div>
