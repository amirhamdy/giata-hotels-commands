<?php

namespace GiataCommands;

use Carbon\Carbon;
use GiataHotels\XmlToArray;
use http\Env\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GiataHotels\API;
use GiataCommands\CommandsHelper;

class InitialCommand extends Command
{
    protected $signature = 'giata:init';

    protected $description = 'Initiate Giata DB Table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $startTime = date("h:i:sa");
        $countries = config('giata-commands.countries');
        $tableName = config('giata-commands.table');
        $columns = config('giata-commands.columns');
        DB::table($tableName)->truncate(); // to be removed
        $this->comment(PHP_EOL . 'working on ' . count($countries) . ' countries');
        foreach ($countries as $country) {
            $response = API::getHotelsByCountry($country, true);
            $this->init($response, $country, $columns, $tableName);
        }
        $endTime = date("h:i:sa");
        $this->comment(PHP_EOL . 'consumed time: ' . CommandsHelper::calcTime($startTime, $endTime));
    }

    protected function init($response, $country, $columns, $tableName)
    {
        $lastGiataId = 0;
        $hotels = $response['property'];
        $this->comment(PHP_EOL . 'working on ' . count($hotels) . ' hotels at ' . $country);
        $bar = $this->output->createProgressBar(count($hotels));
        $bar->start();
        foreach ($hotels as $key => $hotel) {
            $data = [];
            $bar->advance();
            $hotel = XmlToArray::reformatHotel($hotel);
//            dd($hotel);
            $lastGiataId = $hotel['giataId'];
            foreach ($columns as $column) {
                if (isset($hotel[$column])) {
                    $data[$column] = is_array($hotel[$column]) ? json_encode($hotel[$column]) : $hotel[$column];
                }
            }
            if (count($data) > 0) {
                $data['created_at'] = Carbon::now();
                DB::table($tableName)->insert($data);
            }
        }
        $bar->finish();
        $more = isset($response['more']) ? $response['more'] : false;
        if ($more) {
            $response = API::getHotelsByCountry($country, true, $lastGiataId);
            $this->init($response, $country, $columns, $tableName);
        }
    }

}
