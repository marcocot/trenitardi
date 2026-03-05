@props(['stops'])

<div class="rounded-2xl border border-white/10 bg-white/5 shadow-xl backdrop-blur-sm">
    <div class="border-b border-white/5 px-5 py-3">
        <h3 class="text-sm font-semibold text-white">Fermate</h3>
    </div>

    <div class="divide-y divide-white/5">
        @foreach ($stops as $stop)
            @php
                $isPassed = $stop->isPassed();
                $isNext = !$isPassed && $loop->first || (!$isPassed && isset($stops[$loop->index - 1]) && $stops[$loop->index - 1]->isPassed());
            @endphp
            <div class="flex items-center gap-3 px-5 py-3 {{ $isPassed ? 'opacity-50' : '' }}">
                {{-- Timeline Dot --}}
                <div class="relative flex w-5 flex-shrink-0 items-center justify-center">
                    @if ($stop->isOrigin() || $stop->isDestination())
                        <div class="h-3 w-3 rounded-full border-2 {{ $isPassed ? 'border-slate-500 bg-slate-500' : 'border-blue-400 bg-blue-400' }}"></div>
                    @elseif ($isNext)
                        <div class="h-2.5 w-2.5 animate-pulse rounded-full bg-blue-400 ring-2 ring-blue-400/30"></div>
                    @else
                        <div class="h-2 w-2 rounded-full {{ $isPassed ? 'bg-slate-600' : 'bg-slate-500' }}"></div>
                    @endif
                </div>

                {{-- Stop Info --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm {{ $isPassed ? 'text-slate-500' : ($isNext ? 'font-semibold text-white' : 'text-slate-300') }}">
                            {{ $stop->station }}
                        </span>

                        {{-- Platform --}}
                        @php
                            $platform = $stop->actualArrivalPlatform ?? $stop->actualDeparturePlatform ?? $stop->scheduledArrivalPlatform ?? $stop->scheduledDeparturePlatform;
                        @endphp
                        @if ($platform)
                            <span class="flex-shrink-0 rounded bg-slate-700/60 px-1.5 py-0.5 text-xs text-slate-400">
                                Bin. {{ $platform }}
                            </span>
                        @endif
                    </div>

                    {{-- Time / Delay --}}
                    @if ($stop->isPassed() && $stop->departureDelay > 0)
                        <span class="text-xs text-red-400">+{{ $stop->departureDelay }} min</span>
                    @elseif (!$stop->isPassed() && ($stop->scheduledTime || $stop->scheduledDeparturePlatform))
                        @php
                            $time = $stop->scheduledTime ? \Carbon\Carbon::createFromTimestamp(intval($stop->scheduledTime / 1000), 'Europe/Rome')->format('H:i') : null;
                        @endphp
                        @if ($time)
                            <span class="text-xs text-slate-500">{{ $time }}</span>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
