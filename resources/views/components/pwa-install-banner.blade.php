<div
    x-data="{
        show: false,
        init() {
            const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
            const isSafari = /safari/i.test(navigator.userAgent) && !/chrome|crios|fxios|edgios/i.test(navigator.userAgent);
            const isStandalone = window.navigator.standalone === true;
            const dismissed = localStorage.getItem('pwa_banner_dismissed');
            this.show = isIOS && isSafari && !isStandalone && !dismissed;
        },
        dismiss() {
            this.show = false;
            localStorage.setItem('pwa_banner_dismissed', '1');
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    x-cloak
    class="fixed bottom-0 left-0 right-0 z-50 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]"
>
    <div class="mx-auto max-w-sm rounded-2xl border border-white/10 bg-slate-900/95 p-4 shadow-2xl backdrop-blur-md">
        <div class="flex items-start gap-3">
            <img src="/icon.svg" alt="TreniTardi" class="h-12 w-12 flex-shrink-0 rounded-xl">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white">Aggiungi alla schermata Home</p>
                <p class="mt-0.5 text-xs text-slate-400">
                    Tocca
                    <svg class="inline-block h-3.5 w-3.5 align-middle text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    poi <span class="font-medium text-slate-200">"Aggiungi a Home"</span>
                </p>
            </div>
            <button
                @click="dismiss()"
                class="flex-shrink-0 rounded-lg p-1 text-slate-500 transition hover:text-slate-300"
                aria-label="Chiudi"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        {{-- Arrow pointing to the share button at the bottom --}}
        <div class="mt-3 flex items-center justify-center gap-1.5">
            <div class="h-px flex-1 bg-white/10"></div>
            <div class="flex items-center gap-1 text-xs text-slate-500">
                <svg class="h-3 w-3 animate-bounce text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
                <span>barra di Safari in basso</span>
                <svg class="h-3 w-3 animate-bounce text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="h-px flex-1 bg-white/10"></div>
        </div>
    </div>
</div>
