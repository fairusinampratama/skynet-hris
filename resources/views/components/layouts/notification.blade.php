<div 
    x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        timeout: null,
        notify(msg, type = 'success') {
            this.show = false;
            clearTimeout(this.timeout);
            
            setTimeout(() => {
                this.message = msg;
                this.type = type;
                this.show = true;
                
                this.timeout = setTimeout(() => {
                    this.show = false;
                }, 3000);
            }, 100);
        }
    }"
    x-init="
        @if(session()->has('message')) notify('{{ session('message') }}', 'success'); @endif
        @if(session()->has('error')) notify('{{ session('error') }}', 'error'); @endif
    "
    @notify.window="notify($event.detail.message, $event.detail.type)"
    class="fixed inset-x-0 top-6 z-50 flex justify-center pointer-events-none"
>
    <div 
        x-show="show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="pointer-events-auto rounded-full px-6 py-3 shadow-lg flex items-center gap-3 min-w-[300px] max-w-sm mx-4"
        :class="{
            'bg-gray-900 text-white': type === 'success',
            'bg-red-600 text-white': type === 'error' || type === 'danger',
            'bg-blue-600 text-white': type === 'info'
        }"
        style="display: none;"
    >
        <!-- Icons -->
        <template x-if="type === 'success'">
            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </template>
        
        <template x-if="type === 'error' || type === 'danger'">
             <svg class="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </template>

        <p class="text-sm font-semibold" x-text="message"></p>
    </div>
</div>
