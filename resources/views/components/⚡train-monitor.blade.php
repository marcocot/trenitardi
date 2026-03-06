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

        try {
            return TrainStatusDto::fromArray($this->trainStatusData);
        } catch (\InvalidArgumentException) {
            return null;
        }
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

            try {
                TrainStatusDto::fromArray($rawData);
            } catch (\InvalidArgumentException) {
                $this->errorMessage = 'Treno non trovato. Verifica il numero e riprova.';

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
    <x-train-header />
    <x-pwa-install-banner />

    <main class="mx-auto w-full max-w-2xl lg:max-w-[80%] px-4 py-6">

        <x-train-search-card />

        @if ($errorMessage)
            <x-train-error :message="$errorMessage" />
        @endif

        @if ($this->trainStatus)
            <div class="mt-4 space-y-4">
                <x-train-status-card :status="$this->trainStatus" />
                <x-train-stops :stops="$this->trainStatus->stops" />
            </div>
        @endif

        <x-train-favorites />

        @if (! $this->trainStatus && ! $errorMessage && ! $isLoading)
            <x-train-empty-state />
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
