<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use Illuminate\Support\Facades\Http;

class FetchCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('https://restcountries.com/v3.1/all');
        $countries = $response->json();

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['name' => $country['name']['common']],
                ['name' => $country['name']['common']]
            );
        }

        $this->info('Countries fetched and stored successfully.');
    }
}
