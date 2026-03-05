@props(['message'])

<div class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3">
    <div class="flex items-center gap-2">
        <svg class="h-4 w-4 flex-shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm text-red-300">{{ $message }}</p>
    </div>
</div>
