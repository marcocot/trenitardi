@props(['status'])

<div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-xl backdrop-blur-sm">
    {{-- Train Header --}}
    <div class="mb-4 flex items-start justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2">
                <span class="rounded-lg bg-blue-500/20 px-2 py-0.5 text-xs font-semibold text-blue-300">
                    {{ $status->trainLabel ?: 'Treno ' . $status->trainNumber }}
                </span>
                @if ($status->trainType)
                    <span class="rounded-lg bg-slate-700/50 px-2 py-0.5 text-xs text-slate-400">
                        {{ ucfirst($status->trainType) }}
                    </span>
                @endif
            </div>
            <h2 class="text-base font-bold text-white">
                {{ $status->origin }}
                <span class="text-slate-400">→</span>
                {{ $status->destination }}
            </h2>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ $status->scheduledDeparture }} → {{ $status->scheduledArrival }}
            </p>
        </div>

        {{-- Favorites Button --}}
        <button
            @click="toggleFavorite('{{ $status->trainNumber }}')"
            class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 transition hover:border-yellow-500/50 hover:bg-yellow-500/10"
            :title="isFavorite('{{ $status->trainNumber }}') ? 'Rimuovi dai preferiti' : 'Aggiungi ai preferiti'"
        >
            <svg
                class="h-4 w-4 transition"
                :class="isFavorite('{{ $status->trainNumber }}') ? 'text-yellow-400 fill-yellow-400' : 'text-slate-400 fill-none'"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
        </button>
    </div>

    {{-- Status Badge --}}
    <div class="flex items-center gap-3">
        @php
            $delayClass = $status->getDelayClass();
            $statusColors = match($delayClass) {
                'on-time' => ['bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-300', 'dot' => 'bg-emerald-400'],
                'slight-delay' => ['bg' => 'bg-yellow-500/20', 'text' => 'text-yellow-300', 'dot' => 'bg-yellow-400'],
                'moderate-delay' => ['bg' => 'bg-orange-500/20', 'text' => 'text-orange-300', 'dot' => 'bg-orange-400'],
                'severe-delay' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-300', 'dot' => 'bg-red-400'],
                default => ['bg' => 'bg-slate-500/20', 'text' => 'text-slate-300', 'dot' => 'bg-slate-400'],
            };
        @endphp

        <div class="flex items-center gap-2 rounded-full {{ $statusColors['bg'] }} px-3 py-1.5">
            <span class="h-2 w-2 animate-pulse rounded-full {{ $statusColors['dot'] }}"></span>
            <span class="text-sm font-semibold {{ $statusColors['text'] }}">
                {{ $status->getStatusLabel() }}
            </span>
        </div>

        <button
            wire:click="refresh"
            wire:loading.attr="disabled"
            class="ml-auto flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-400 transition hover:border-white/20 hover:text-white"
        >
            <svg wire:loading.remove wire:target="refresh" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg wire:loading wire:target="refresh" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Aggiorna
        </button>
    </div>

    {{-- Last Detection --}}
    @if ($status->lastDetectedStation)
        <div class="mt-3 flex items-center gap-2 rounded-lg border border-white/5 bg-white/3 px-3 py-2">
            <svg class="h-3.5 w-3.5 flex-shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-xs text-slate-400">
                Ultimo rilevamento:
                <span class="font-medium text-slate-200">{{ $status->lastDetectedStation }}</span>
                @if ($status->lastDetectedTime)
                    <span class="text-slate-500"> alle {{ $status->lastDetectedTime }}</span>
                @endif
            </span>
        </div>
    @endif

    {{-- Next Stop --}}
    @if ($status->isRunning && ($nextStop = $status->getNextStop()))
        @php
            $toRome = fn (int $ms) => \Carbon\Carbon::createFromTimestamp(intval($ms / 1000), 'Europe/Rome')->format('H:i');

            $scheduledArrival = $nextStop->scheduledTime ? $toRome($nextStop->scheduledTime) : null;

            $effectiveDelay = $nextStop->arrivalDelay ?: $nextStop->delay ?: $status->delay;

            $estimatedArrival = $nextStop->actualTime
                ? $toRome($nextStop->actualTime)
                : ($scheduledArrival && $effectiveDelay > 0
                    ? $toRome($nextStop->scheduledTime + $effectiveDelay * 60000)
                    : null);

            $isDelayedArrival = $estimatedArrival && $estimatedArrival !== $scheduledArrival;
        @endphp
        <div class="mt-3 rounded-lg border border-blue-500/20 bg-blue-500/5 px-3 py-2.5">
            <p class="mb-1.5 text-xs font-medium text-blue-400">Prossima fermata</p>
            <div class="flex items-center justify-between gap-3">
                <span class="truncate text-sm font-semibold text-white">{{ $nextStop->station }}</span>
                @if ($scheduledArrival)
                    <div class="flex flex-shrink-0 items-center gap-2 text-xs">
                        <span class="text-slate-400">
                            Previsto:
                            <span class="font-medium text-slate-200">{{ $scheduledArrival }}</span>
                        </span>
                        @if ($estimatedArrival)
                            <span class="{{ $isDelayedArrival ? 'text-red-400' : 'text-emerald-400' }}">
                                Stimato:
                                <span class="font-medium">{{ $estimatedArrival }}</span>
                            </span>
                        @endif
                        @if ($effectiveDelay > 0)
                            <span class="rounded bg-red-500/20 px-1.5 py-0.5 font-semibold text-red-400">
                                +{{ $effectiveDelay }} min
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
