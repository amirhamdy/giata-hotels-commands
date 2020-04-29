<?php

namespace GiataCommands;

use Carbon\Carbon;
use GiataHotels\API;
use GiataHotels\XmlToArray;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateCommand extends Command
{
    protected $signature = 'giata:translate';

    protected $description = 'Translate Giata DB Table!';

    public function __construct()
    {
        parent::__construct();
        DB::connection()->enableQueryLog();
    }

    public function handle()
    {
        $tableName = config('giata-commands.table');
        $translationTableName = config('giata-commands.translation.table');

        // to be removed
        DB::table($translationTableName)->truncate();

        DB::table($tableName)->select('giataId')->orderBy('created_at')->chunk(200, function ($hotels) {
            $startTime = date("h:i:sa");
            $languages = config('giata-commands.translation.languages');
            $columns = config('giata-commands.translation.columns');
            $translationTableName = config('giata-commands.translation.table');
            $date = config('giata-commands.translation.date');
            $this->comment(PHP_EOL . 'working on ' . count($hotels) . ' hotels');
            $bar = $this->output->createProgressBar(count($hotels));
            $bar->start();

            foreach ($hotels as $hotel) {
                $bar->advance();
                foreach ($languages as $language) {
//                    $this->comment(PHP_EOL . 'working on (' . $language . ') language for (giataId: ' . $hotel->giataId . ').');
                    $response = API::getTextsByGiataId($hotel->giataId, $language);
                    if (isset($response['status']) && $response['status'] != 200) {
                        $this->error(PHP_EOL . $response['error']);
                        continue;
                    }
                    $texts = $response['item'];
                    $data = [];
                    $texts = XmlToArray::reformatHotelTexts($texts);
                    foreach ($columns as $column) {
                        if (isset($texts[$column])) {
                            $data[$column] = is_array($texts[$column]) ? json_encode($texts[$column], JSON_UNESCAPED_UNICODE) : $texts[$column];
                        }
                    }
                    if (count($data) > 0) {
                        $data['created_at'] = Carbon::now();
                        DB::table($translationTableName)->insert($data);
                    }
                }
            }
            $bar->finish();
            $endTime = date("h:i:sa");
            $this->comment(PHP_EOL . 'consumed time: ' . CommandsHelper::calcTime($startTime, $endTime));
        });
    }
}
