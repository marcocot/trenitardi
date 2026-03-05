<div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-xl backdrop-blur-sm">
    <h1 class="mb-1 text-lg font-bold text-white">Cerca Treno</h1>
    <p class="mb-4 text-sm text-slate-400">Inserisci il numero del treno per monitorarne lo stato in tempo reale</p>

    <form wire:submit="search" class="flex gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                type="text"
                wire:model="trainNumber"
                placeholder="es. 9642"
                maxlength="10"
                class="w-full rounded-xl border border-white/10 bg-white/5 py-3 pl-10 pr-4 text-white placeholder-slate-500 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
            >
        </div>
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="flex items-center gap-2 rounded-xl bg-blue-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-400 active:scale-95 disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="search">Cerca</span>
            <span wire:loading wire:target="search">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </span>
        </button>
    </form>

    @error('trainNumber')
        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
    @enderror
</div>
