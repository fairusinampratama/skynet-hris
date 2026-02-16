
<div class="max-w-2xl mx-auto p-6 bg-white shadow rounded-lg">
    <h1 class="text-2xl font-bold mb-6">Request Overtime</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="submit">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Date</label>
            <input type="date" wire:model="date" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Hours</label>
            <input type="number" step="0.5" wire:model="hours" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('hours') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Reason</label>
            <textarea wire:model="reason" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3"></textarea>
            @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                Submit Request
            </button>
        </div>
    </form>
    
    <div class="mt-8">
        <h2 class="font-semibold mb-2">Recent Requests</h2>
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hours</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myRequests as $req)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $req->date->format('Y-m-d') }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $req->hours }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight 
                            {{ $req->status === 'approved' ? 'text-green-900' : ($req->status === 'rejected' ? 'text-red-900' : 'text-orange-900') }}">
                            <span aria-hidden class="absolute inset-0 opacity-50 rounded-full 
                                {{ $req->status === 'approved' ? 'bg-green-200' : ($req->status === 'rejected' ? 'bg-red-200' : 'bg-orange-200') }}"></span>
                            <span class="relative">{{ ucfirst($req->status) }}</span>
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
