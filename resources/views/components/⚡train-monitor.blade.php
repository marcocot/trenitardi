<?php

use App\DTOs\TrainStatusDto;
use App\Services\TrainService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|min:1|max:10')]
    public string $trainNumber = '';

    /** @var array<string, mixed>|null */
    public ?array $trainStatusData = null;
    public bool $isLoading = false;
    public ?string $errorMessage = null;

    #[Computed]
    public function trainStatus(): ?TrainStatusDto
    {
        if ($this->trainStatusData === null) {
            return null;
        }

        return TrainStatusDto::fromArray($this->trainStatusData);
    }

    public function mount(?string $trainNumber = null): void
    {
        if ($trainNumber !== null) {
            $this->trainNumber = $trainNumber;
            $this->fetchData();
        }
    }

    public function search(): void
    {
        $this->validate();

        $fetched = $this->fetchData();

        if ($fetched) {
            $this->redirect(route('train.status', ['trainNumber' => $this->trainNumber]));
        }
    }

    public function refresh(): void
    {
        if ($this->trainNumber !== '') {
            $this->fetchData();
        }
    }

    private function fetchData(): bool
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        $this->trainStatusData = null;

        try {
            $service = app(TrainService::class);
            $searchResult = $service->searchTrain($this->trainNumber);

            if ($searchResult === null) {
                $this->errorMessage = 'Treno non trovato. Verifica il numero e riprova.';

                return false;
            }

            $rawData = $service->getRawTrainStatus(
                $searchResult->trainId,
                $searchResult->trainNumber,
                $searchResult->timestamp,
            );

            if ($rawData === null) {
                $this->errorMessage = 'Impossibile recuperare lo stato del treno.';

                return false;
            }

            $this->trainStatusData = $rawData;

            return true;
        } catch (\Exception) {
            $this->errorMessage = 'Errore di connessione. Riprova tra qualche istante.';

            return false;
        } finally {
            $this->isLoading = false;
        }
    }
};
?>

<div
    class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900"
    x-data="trainFavorites()"
    x-init="init()"
>
    {{-- Header --}}
    <header class="sticky top-0 z-10 border-b border-white/5 bg-slate-950/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-2xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white">TrainTrack</span>
            </div>
            <span class="text-xs text-slate-500">Monitor Treni Italia</span>
        </div>
    </header>

    <main class="mx-auto w-full max-w-2xl lg:max-w-[80%] px-4 py-6">

        {{-- Search Card --}}
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

        {{-- Error Message --}}
        @if ($errorMessage)
            <div class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 flex-shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-red-300">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        {{-- Train Status Card --}}
        @if ($this->trainStatus)
            <div class="mt-4 space-y-4">

                {{-- Main Status Card --}}
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-xl backdrop-blur-sm">
                    {{-- Train Header --}}
                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <div class="mb-1 flex items-center gap-2">
                                <span class="rounded-lg bg-blue-500/20 px-2 py-0.5 text-xs font-semibold text-blue-300">
                                    {{ $this->trainStatus->trainLabel ?: 'Treno ' . $this->trainStatus->trainNumber }}
                                </span>
                                @if ($this->trainStatus->trainType)
                                    <span class="rounded-lg bg-slate-700/50 px-2 py-0.5 text-xs text-slate-400">
                                        {{ ucfirst($this->trainStatus->trainType) }}
                                    </span>
                                @endif
                            </div>
                            <h2 class="text-base font-bold text-white">
                                {{ $this->trainStatus->origin }}
                                <span class="text-slate-400">→</span>
                                {{ $this->trainStatus->destination }}
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $this->trainStatus->scheduledDeparture }} → {{ $this->trainStatus->scheduledArrival }}
                            </p>
                        </div>

                        {{-- Favorites Button --}}
                        <button
                            @click="toggleFavorite('{{ $this->trainStatus->trainNumber }}')"
                            class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 transition hover:border-yellow-500/50 hover:bg-yellow-500/10"
                            :title="isFavorite('{{ $this->trainStatus->trainNumber }}') ? 'Rimuovi dai preferiti' : 'Aggiungi ai preferiti'"
                        >
                            <svg
                                class="h-4 w-4 transition"
                                :class="isFavorite('{{ $this->trainStatus->trainNumber }}') ? 'text-yellow-400 fill-yellow-400' : 'text-slate-400 fill-none'"
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
                            $delayClass = $this->trainStatus->getDelayClass();
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
                                {{ $this->trainStatus->getStatusLabel() }}
                            </span>
                        </div>

                        @if ($this->trainStatus->delay > 0)
                            <span class="text-sm font-bold text-red-400">+{{ $this->trainStatus->delay }} min</span>
                        @endif

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
                    @if ($this->trainStatus->lastDetectedStation)
                        <div class="mt-3 flex items-center gap-2 rounded-lg border border-white/5 bg-white/3 px-3 py-2">
                            <svg class="h-3.5 w-3.5 flex-shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-xs text-slate-400">
                                Ultimo rilevamento:
                                <span class="font-medium text-slate-200">{{ $this->trainStatus->lastDetectedStation }}</span>
                                @if ($this->trainStatus->lastDetectedTime)
                                    <span class="text-slate-500"> alle {{ $this->trainStatus->lastDetectedTime }}</span>
                                @endif
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Stops List --}}
                <div class="rounded-2xl border border-white/10 bg-white/5 shadow-xl backdrop-blur-sm">
                    <div class="border-b border-white/5 px-5 py-3">
                        <h3 class="text-sm font-semibold text-white">Fermate</h3>
                    </div>

                    <div class="divide-y divide-white/5">
                        @foreach ($this->trainStatus->stops as $stop)
                            @php
                                $isPassed = $stop->isPassed();
                                $isNext = !$isPassed && $loop->first || (!$isPassed && isset($this->trainStatus->stops[$loop->index - 1]) && $this->trainStatus->stops[$loop->index - 1]->isPassed());
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

                                    {{-- Delay for stop --}}
                                    @if ($stop->isPassed() && $stop->departureDelay > 0)
                                        <span class="text-xs text-red-400">+{{ $stop->departureDelay }} min</span>
                                    @elseif (!$stop->isPassed() && ($stop->scheduledTime || $stop->scheduledDeparturePlatform))
                                        @php
                                            $time = $stop->scheduledTime ? date('H:i', intval($stop->scheduledTime / 1000)) : null;
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

            </div>
        @endif

        {{-- Favorites Section --}}
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

        {{-- Empty State --}}
        @if (! $this->trainStatus && ! $errorMessage && ! $isLoading)
            <div class="mt-10 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5">
                    <svg class="h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-500">Inserisci un numero di treno per iniziare</p>
                <p class="mt-1 text-xs text-slate-600">Puoi monitorare ritardi, fermate e posizione in tempo reale</p>
            </div>
        @endif

    </main>

    <script>
        function trainFavorites() {
            return {
                favorites: [],

                init() {
                    const stored = localStorage.getItem('train_favorites');
                    this.favorites = stored ? JSON.parse(stored) : [];
                },

                isFavorite(trainNumber) {
                    return this.favorites.includes(String(trainNumber));
                },

                toggleFavorite(trainNumber) {
                    const num = String(trainNumber);
                    if (this.isFavorite(num)) {
                        this.removeFavorite(num);
                    } else {
                        this.addFavorite(num);
                    }
                },

                addFavorite(trainNumber) {
                    const num = String(trainNumber);
                    if (!this.favorites.includes(num)) {
                        this.favorites.push(num);
                        this.persist();
                    }
                },

                removeFavorite(trainNumber) {
                    this.favorites = this.favorites.filter(f => f !== String(trainNumber));
                    this.persist();
                },

                persist() {
                    localStorage.setItem('train_favorites', JSON.stringify(this.favorites));
                },
            };
        }
    </script>
</div>
