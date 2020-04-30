<?php

namespace GiataCommands;

use Carbon\Carbon;
use GiataCommands\CommandsHelper;
use GiataAPI;
use GiataHotels\XmlToArray;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCommand extends Command
{
    protected $signature = 'giata:update';

    protected $description = 'Update Giata DB Tables!';

    public function __construct()
    {
        parent::__construct();
        DB::connection()->enableQueryLog();
    }

    public function handle()
    {
        $tableName = config('giata-commands.table');
        $columns = config('giata-commands.columns');
        $date = config('giata-commands.date');
        DB::table($tableName)->select($columns)->orderBy('created_at')->chunk(500, function ($hotels) {
            $startTime = date("h:i:sa");
            $columns = config('giata-commands.columns');
            $tableName = config('giata-commands.table');
            $bar = $this->output->createProgressBar(count($hotels));
            $bar->start();
            foreach ($hotels as $hotel) {
                $bar->advance();
                $hotel = json_decode(json_encode($hotel), true);
                $response = GiataAPI::getHotelByGiataId($hotel['giataId']);
                $hotelUpdated = XmlToArray::reformatHotel($response['property']);
                $data = [];
                foreach ($columns as $column) {
                    if (isset($hotelUpdated[$column])) {
                        $columnUpdated = is_array($hotelUpdated[$column]) ? json_encode($hotelUpdated[$column]) : $hotelUpdated[$column];
                        if ($hotel[$column] != $columnUpdated) {
                            $data[$column] = $columnUpdated;
                        }
                    }
                }
                if (count($data) > 0) {
                    $data['updated_at'] = Carbon::now();
                    DB::table($tableName)->where('giataId', $hotel['giataId'])->update($data);
                    $queries = DB::getQueryLog();
                    $this->comment(end($queries)['query']);
                }
            }
            $bar->finish();
            $endTime = date("h:i:sa");
            $this->comment(PHP_EOL . 'consumed time: ' . CommandsHelper::calcTime($startTime, $endTime));
        });
    }
}
