<?php

namespace GiataCommands;

use Carbon\Carbon;
use GiataHotels\XmlToArray;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GiataAPI;

class ProviderIDsCommand extends Command
{
    protected $signature = 'giata:map';

    protected $description = 'Map Giata Providers IDs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $startTime = date("h:i:sa");
        $providers = config('giata-commands.providers.names');
        $tableName = config('giata-commands.providers.table');
        $columns = config('giata-commands.providers.columns');
        DB::table($tableName)->truncate(); // to be removed
        foreach ($providers as $count => $provider) {
            $this->comment(PHP_EOL . 'working on ' . $providers[$count] . ' number ' . ($count + 1) . ' of ' . count($providers) . ' providers');
            $response = GiataAPI::getHotelsProviderIDs($provider);
//            $response = file_get_contents(public_path('response.xml'));
//            $response = XmlToArray::convert($response);
            $this->init($response, $provider, $columns, $tableName);
        }
        $endTime = date("h:i:sa");
        $this->comment(PHP_EOL . 'consumed time: ' . CommandsHelper::calcTime($startTime, $endTime));
    }

    protected function init($response, $provider, $columns, $tableName)
    {
        $hotels = $response['items']['item'];
        $this->comment(PHP_EOL . 'working on ' . count($hotels) . ' hotels at ' . $provider);
        $bar = $this->output->createProgressBar(count($hotels));
        $bar->start();
        foreach ($hotels as $hotel) {
            $bar->advance();
            $hotel = XmlToArray::reformatProviderIds($hotel);
            foreach ($hotel['code'] as $providerId) {
                $data = [];
                $data['giataId'] = $hotel['giataId'];
                $data['providerId'] = $providerId;
                $data['provider'] = $provider;
                DB::table($tableName)->insert($data);
            }
        }
        $bar->finish();
    }
}
