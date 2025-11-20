<?php

namespace App\Console\Commands;

use App\Imports\AmpleursImport;
use Illuminate\Console\Command;
use App\Imports\RepondansImport;
use App\Imports\MinisteresImport;
use App\Services\Imports\ImportData;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use ReflectionClass; // Importez la classe ReflectionClass

class ImportExcel extends Command
{
    protected $signature = 'import:excel';
    protected $description = 'Laravel Excel importer';

    public function handle()
    {
        $this->output->title('Importation en cours ...');
        ImportData::import();
        $this->output->success('Importation effectuée');
    }
}