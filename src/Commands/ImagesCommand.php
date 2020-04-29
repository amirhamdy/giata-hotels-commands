<?php

namespace GiataCommands;

use Carbon\Carbon;
use GiataHotels\API;
use GiataHotels\XmlToArray;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagesCommand extends Command
{
    protected $signature = 'giata:images';

    protected $description = 'Add Giata Hotel\'s Images to DB Table!';

    public function __construct()
    {
        parent::__construct();
        DB::connection()->enableQueryLog();
    }

    public function handle()
    {
        $tableName = config('giata-commands.table');
        $imagesTableName = config('giata-commands.images.table');

        // to be removed
        DB::table($imagesTableName)->truncate();

        DB::table($tableName)->select('giataId')->orderBy('created_at')->chunk(300, function ($hotels) {
            $startTime = date("h:i:sa");
            $drive = config('giata-commands.images.drive');
            $columns = config('giata-commands.images.columns');
            $imagesTableName = config('giata-commands.images.table');
            $this->comment(PHP_EOL . 'working on ' . count($hotels) . ' hotels');
            $bar = $this->output->createProgressBar(count($hotels));
            $bar->start();

            foreach ($hotels as $hotel) {
                $bar->advance();
                $response = API::getImagesByGiataId($hotel->giataId);
                if (isset($response['status']) && $response['status'] != 200) {
                    $this->comment(PHP_EOL . $response['error']);
                    continue;
                }
                $images = $response['item'];
                $data = [];
                $images = XmlToArray::reformatHotelImages($images);

//                $uploaded = $this->uploadToDrive($drive, $images['images'], 'GIATA_TESTING');

                foreach ($columns as $column) {
                    if (isset($images[$column])) {
                        $data[$column] = is_array($images[$column]) ? json_encode($images[$column], JSON_UNESCAPED_UNICODE) : $images[$column];
                    }
                }
                if (count($data) > 0) {
                    $data['created_at'] = Carbon::now();
                    DB::table($imagesTableName)->insert($data);
                }
            }
            $bar->finish();
            $endTime = date("h:i:sa");
            $this->comment(PHP_EOL . 'consumed time: ' . CommandsHelper::calcTime($startTime, $endTime));
        });
    }

    protected function uploadToDrive($drive, $urls, $path)
    {
        // to be added...
    }
}
