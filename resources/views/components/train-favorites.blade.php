<div class="mt-6" x-show="favorites.length > 0" x-cloak>
    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-400">
        <svg class="h-4 w-4 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
        Preferiti
    </h2>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        <template x-for="fav in favorites" :key="fav">
            <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 transition hover:border-white/20">
                <button
                    class="flex-1 text-left"
                    @click="window.location.href = '/status/' + fav"
                >
                    <span class="block text-xs text-slate-500">Treno</span>
                    <span class="text-sm font-semibold text-white" x-text="fav"></span>
                </button>
                <button
                    @click="removeFavorite(fav)"
                    class="ml-2 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-md text-slate-500 transition hover:bg-red-500/10 hover:text-red-400"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>
