
<div class="max-w-2xl mx-auto p-6 bg-white shadow rounded-lg">
    <h1 class="text-2xl font-bold mb-6">Request Leave</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6">
        <h2 class="font-semibold mb-2">My Balances</h2>
        <div class="grid grid-cols-2 gap-4">
            @foreach($balances as $balance)
                <div class="bg-gray-50 p-3 rounded">
                    <span class="block text-sm text-gray-600">{{ $balance->leaveType->name }}</span>
                    <span class="block text-xl font-bold">
                        {{ $balance->remaining_days ?? '∞' }} Days
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <form wire:submit="submit">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Leave Type</label>
            <select wire:model="leave_type_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Select Type</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('leave_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                <input type="date" wire:model="start_date" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">End Date</label>
                <input type="date" wire:model="end_date" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Reason</label>
            <textarea wire:model="reason" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3"></textarea>
            @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Attachment (Optional/Required for Sick Leave)</label>
            <input type="file" wire:model="attachment" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                Submit Request
            </button>
        </div>
    </form>
</div>
