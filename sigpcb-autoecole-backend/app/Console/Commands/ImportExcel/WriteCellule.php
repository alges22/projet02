<?php

namespace App\Console\Commands\ImportExcel;

use Exception;
use Illuminate\Console\Command;
use App\Imports\AutoEcoleImports;
use App\Imports\GroupementsImport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Services\Imports\ExcelCellHandler;

class WriteCellule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cellule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->output->title('Création de la FromExcellRow ...');

        # Créaion des cellules pour l'importation si nécessaire
        $excelPath = Storage::path('excel/import.xlsx');
        if (!File::exists($excelPath)) {
            throw new Exception("Le fichier excel des auto-écoles n'existe pas", 1);
        }
        $path = storage_path('app/excel/chunks/1.json');

        if (!is_dir(storage_path('app/excel/chunks'))) {
            mkdir(storage_path('app/excel/chunks'), 0777, true);
        }
        $path = storage_path('app/excel/chunks/1.json');
        if (!file_exists($path)) {
            $this->info("Découpage du fichier Excel en morceau");
            Excel::import(new AutoEcoleImports, Storage::path('excel/import.xlsx'));
            $this->info("Découpage terminée");
        }
        $contents =  json_decode(File::get($path), true);

        $cellues = $contents[0];
        ExcelCellHandler::createClass($cellues);
        $this->output->success('Création effectuée');
        return Command::SUCCESS;
    }
}
