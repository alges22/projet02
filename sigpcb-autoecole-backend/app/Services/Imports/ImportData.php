<?php

namespace App\Services\Imports;


use App\Services\Help;
use DirectoryIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportData
{
    private $chunks = array();

    private $images = [];

    public function __construct()
    {
        $path = storage_path('app/excel/chunks');
        $directoryIterator = new DirectoryIterator($path);

        foreach ($directoryIterator as $fileInfo) {
            // Vérifie si le chemin est un fichier (et non un répertoire)
            if ($fileInfo->isFile()) {
                // Récupère le nom du fichier
                $filename = $path . '/' . $fileInfo->getFilename();

                // Ajoute le nom du fichier à la liste des chunks
                $this->chunks[] = $filename;
            }
        }
    }
    private function _import()
    {

        foreach ($this->chunks as   $fileJson) {

            $collections = collect(file_get_contents($fileJson, true) ?? []);
            if ($collections->isNotEmpty()) {
                foreach ($collections as $key => $c) {
                    $collection = collect(json_decode($c, true));


                    $cellues = $collection->shift();

                    $dataCollection = $this->notNullProjects($collection->values());
                    if ($cellues) {
                        foreach ($dataCollection as $key => $row) {
                            $this->OnEachRow($row);
                        }
                    }
                }
            }
        }
        return [];
    }

    private function OnEachRow($row)
    {
        DB::beginTransaction();
        $rowInstance = new FromExcelRow($row);
        try {
            (new ExcelImportation($rowInstance))->create();

            DB::commit();
        } catch (\Throwable $th) {
            $message = "L'auto-école: {$rowInstance->getAutoEcole()}";
            Help::log($message . "\t\n {$th->getMessage()} ", 'errors.txt');
            DB::rollBack();
            throw $th;
        }
    }

    private function notNullProjects(Collection $collection)
    {
        return $collection->filter(function ($row) {
            return !is_null($row[1]);
        });
    }

    public static function import()
    {
        return (new static())->_import();
    }
}
