<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TrainMonitorTest extends TestCase
{
    private function fakeSuccessfulApiCalls(string $trainNumber = '9642'): void
    {
        Http::fake([
            "*cercaNumeroTrenoTrenoAutocomplete/{$trainNumber}*" => Http::response(
                "{$trainNumber} - REGGIO DI CALABRIA CENTRALE - 05/03/26|{$trainNumber}-S11781-1772665200000",
                200,
            ),
            '*andamentoTreno/S11781/*' => Http::response(
                json_encode($this->sampleStatusPayload()),
                200,
            ),
        ]);
    }

    private function sampleStatusPayload(array $overrides = []): array
    {
        return array_merge([
            'numeroTreno' => 9642,
            'compNumeroTreno' => ' FR 9642',
            'origine' => 'REGGIO DI CALABRIA CENTRALE',
            'destinazione' => 'TORINO PORTA NUOVA',
            'compTipologiaTreno' => 'nazionale',
            'circolante' => true,
            'arrivato' => false,
            'nonPartito' => false,
            'ritardo' => 0,
            'compOrarioPartenzaZero' => '08:40',
            'compOrarioArrivoZero' => '19:18',
            'stazioneUltimoRilevamento' => 'BATTIPAGLIA',
            'compOraUltimoRilevamento' => '12:32',
            'compRitardo' => ['in orario'],
            'oraUltimoRilevamento' => 1772710320000,
            'fermate' => [],
        ], $overrides);
    }

    // --- Page rendering ---

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_status_page_renders_successfully(): void
    {
        $this->fakeSuccessfulApiCalls();

        $response = $this->get('/status/9642');

        $response->assertOk();
    }

    // --- Initial state ---

    public function test_component_initializes_with_empty_state(): void
    {
        Livewire::test('train-monitor')
            ->assertSet('trainNumber', '')
            ->assertSet('trainStatusData', null)
            ->assertSet('errorMessage', null)
            ->assertSet('isLoading', false);
    }

    // --- mount() with URL parameter ---

    public function test_mount_with_train_number_fetches_data(): void
    {
        $this->fakeSuccessfulApiCalls();

        $component = Livewire::test('train-monitor', ['trainNumber' => '9642'])
            ->assertSet('trainNumber', '9642');

        $this->assertNotNull($component->get('trainStatusData'));
    }

    public function test_mount_without_train_number_does_not_fetch(): void
    {
        Http::fake();

        Livewire::test('train-monitor')
            ->assertSet('trainStatusData', null);

        Http::assertNothingSent();
    }

    public function test_mount_with_invalid_train_sets_error(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/0000*' => Http::response('', 200),
        ]);

        $component = Livewire::test('train-monitor', ['trainNumber' => '0000'])
            ->assertSet('trainStatusData', null);

        $this->assertNotNull($component->get('errorMessage'));
    }

    // --- search() ---

    public function test_search_with_valid_train_number_redirects_to_status_page(): void
    {
        $this->fakeSuccessfulApiCalls();

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertRedirect(route('train.status', ['trainNumber' => '9642']));
    }

    public function test_search_with_valid_train_number_loads_data(): void
    {
        $this->fakeSuccessfulApiCalls();

        $component = Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search');

        $this->assertSame(
            'REGGIO DI CALABRIA CENTRALE',
            $component->get('trainStatusData')['origine'],
        );
    }

    public function test_search_without_train_number_shows_validation_error(): void
    {
        Livewire::test('train-monitor')
            ->set('trainNumber', '')
            ->call('search')
            ->assertHasErrors(['trainNumber']);
    }

    public function test_search_clears_previous_error_message(): void
    {
        $this->fakeSuccessfulApiCalls();

        Livewire::test('train-monitor')
            ->set('errorMessage', 'previous error')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('errorMessage', null);
    }

    public function test_search_clears_previous_train_status_on_failure(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/9642*' => Http::response(
                '9642 - REGGIO - 05/03/26|9642-S11781-1772665200000',
                200,
            ),
            '*andamentoTreno/*' => Http::response('', 500),
        ]);

        $component = Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search');

        $this->assertNull($component->get('trainStatusData'));
    }

    public function test_search_sets_error_when_train_not_found(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response('', 200),
        ]);

        Livewire::test('train-monitor')
            ->set('trainNumber', '9999')
            ->call('search')
            ->assertSet('trainStatusData', null)
            ->assertSet('errorMessage', 'Treno non trovato. Verifica il numero e riprova.');
    }

    public function test_search_sets_error_when_status_api_fails(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response(
                '9642 - REGGIO - 05/03/26|9642-S11781-1772665200000',
                200,
            ),
            '*andamentoTreno/*' => Http::response('', 500),
        ]);

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('trainStatusData', null)
            ->assertSet('errorMessage', 'Impossibile recuperare lo stato del treno.');
    }

    public function test_search_sets_connection_error_on_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Connection refused');
        });

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('errorMessage', 'Errore di connessione. Riprova tra qualche istante.');
    }

    public function test_search_resets_is_loading_to_false_after_success(): void
    {
        $this->fakeSuccessfulApiCalls();

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('isLoading', false);
    }

    public function test_search_resets_is_loading_to_false_after_failure(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response('', 200),
        ]);

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('isLoading', false);
    }

    // --- refresh() ---

    public function test_refresh_fetches_updated_data(): void
    {
        $this->fakeSuccessfulApiCalls();

        // First load via mount to get valid initial state
        $component = Livewire::test('train-monitor', ['trainNumber' => '9642']);

        $this->fakeSuccessfulApiCalls();

        $refreshed = $component->call('refresh');

        $this->assertNotNull($refreshed->get('trainStatusData'));
    }

    public function test_refresh_does_nothing_when_train_number_empty(): void
    {
        Http::fake();

        Livewire::test('train-monitor')
            ->set('trainNumber', '')
            ->call('refresh')
            ->assertSet('trainStatusData', null);

        Http::assertNothingSent();
    }

    // --- Computed trainStatus ---

    public function test_train_status_computed_property_returns_null_when_no_data(): void
    {
        $component = Livewire::test('train-monitor');

        $this->assertNull($component->instance()->trainStatus);
    }

    public function test_train_status_computed_property_returns_dto_when_data_set(): void
    {
        $this->fakeSuccessfulApiCalls();

        $component = Livewire::test('train-monitor', ['trainNumber' => '9642']);

        $this->assertNotNull($component->instance()->trainStatus);
        $this->assertSame('REGGIO DI CALABRIA CENTRALE', $component->instance()->trainStatus->origin);
    }

    // --- View rendering ---

    public function test_deeplink_auto_loads_and_shows_train_info(): void
    {
        $this->fakeSuccessfulApiCalls();

        Livewire::test('train-monitor', ['trainNumber' => '9642'])
            ->assertSee('FR 9642')
            ->assertSee('REGGIO DI CALABRIA CENTRALE')
            ->assertSee('TORINO PORTA NUOVA');
    }

    public function test_component_shows_error_message_in_view(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response('', 200),
        ]);

        Livewire::test('train-monitor')
            ->set('trainNumber', '9999')
            ->call('search')
            ->assertSee('Treno non trovato. Verifica il numero e riprova.');
    }

    public function test_search_route_returns_ok(): void
    {
        $this->fakeSuccessfulApiCalls();

        $this->get(route('train.status', ['trainNumber' => '9642']))->assertOk();
    }

    // --- Malformed / invalid API data ---

    public function test_search_does_not_crash_when_status_api_returns_empty_origin(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/9642*' => Http::response(
                '9642 - REGGIO - 05/03/26|9642-S11781-1772665200000',
                200,
            ),
            '*andamentoTreno/*' => Http::response(
                json_encode($this->sampleStatusPayload(['origine' => ''])),
                200,
            ),
        ]);

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('trainStatusData', null)
            ->assertSet('errorMessage', 'Treno non trovato. Verifica il numero e riprova.');
    }

    public function test_search_does_not_crash_when_status_api_returns_zero_train_number(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/9642*' => Http::response(
                '9642 - REGGIO - 05/03/26|9642-S11781-1772665200000',
                200,
            ),
            '*andamentoTreno/*' => Http::response(
                json_encode($this->sampleStatusPayload(['numeroTreno' => 0])),
                200,
            ),
        ]);

        Livewire::test('train-monitor')
            ->set('trainNumber', '9642')
            ->call('search')
            ->assertSet('trainStatusData', null)
            ->assertSet('errorMessage', 'Treno non trovato. Verifica il numero e riprova.');
    }

    public function test_deeplink_with_text_input_shows_error_not_500(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/test*' => Http::response('', 200),
        ]);

        $response = $this->get('/status/test');

        $response->assertOk();
    }

    public function test_component_does_not_crash_when_train_status_data_is_malformed(): void
    {
        $component = Livewire::test('train-monitor');

        // Force-set malformed data directly (simulates corrupted state)
        $component->set('trainStatusData', ['numeroTreno' => 0, 'origine' => '', 'destinazione' => '']);

        $this->assertNull($component->instance()->trainStatus);
    }
}
