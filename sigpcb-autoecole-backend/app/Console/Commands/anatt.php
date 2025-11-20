<?php

namespace App\Console\Commands;

use App\Services\Imports\UserImportation;
use Illuminate\Console\Command;

class anatt extends Command
{

    private $datas = [
        'users' => UserImportation::class,
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anatt:data {data} {--export} {--import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exportation des données';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = $this->argument('data');

        $isImport = $this->option('import');

        $type = boolval($isImport) ? 'import' : 'export';

        if (array_key_exists($data, $this->datas)) {
            $class = $this->datas[$data];
            if (class_exists($class)) {
                //Exécution de la classe
                app()->make($class, ["type" => $type]);
            }
        }
        return Command::SUCCESS;
    }
}
