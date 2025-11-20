<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\AutoEcoleImports;
use Illuminate\Support\Facades\DB;
use App\Services\Imports\ImportData;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Services\Imports\ExcelCellHandler;
use App\Services\Exception\ImportationException;

class ImportController extends ApiController
{
    public function autoEcoles(Request $request)
    {

        DB::beginTransaction();
        try {
            # Créaion des cellules pour l'importation si nécessaire
            $excelPath = Storage::path('excel/import.xlsx');
            if (!File::exists($excelPath)) {
                return $this->errorResponse("Le fichier excel des auto-écoles n'existe pas");
            }
            $cellulePath = storage_path('app/excel/chunks/1.json');

            if (!is_dir(storage_path('app/excel/chunks'))) {
                mkdir(storage_path('app/excel/chunks'), 0777, true);
            }
            if (!file_exists($cellulePath)) {
                Excel::import(new AutoEcoleImports, Storage::path('excel/import.xlsx'));
            }
            $contents =  json_decode(File::get($cellulePath), true);

            $cellues = $contents[0];
            ExcelCellHandler::createClass($cellues);

            # Fin de la création des cellules
            # Importation des données
            ImportData::import();

            DB::commit();

            return $this->successResponse("Importation des auto-écoles effectuée avec succès");
        } catch (ImportationException $th) {

            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse($th->getMessage());
        } catch (\Throwable $th) {

            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse($th->getMessage());
        }
    }
}
