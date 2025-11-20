<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\AutoEcoleImports;
use Illuminate\Support\Facades\DB;
use App\Imports\ExaminateurImports;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ImportationException;
use App\Services\Imports\Validation\AutoEcoleCellsValidation;
use App\Services\Imports\Validation\ExaminateurCellsValidation;

class ImportController extends ApiController
{
    public function autoEcoles(Request $request)
    {
        $this->hasAnyPermission(["all","edit-driving-school-management"]);

        DB::connection("base")->beginTransaction();
        $name = now()->format("d-m-Y H-s-i-") . 'auto-ecoles.xlsx';
        $filePath = storage_path('app/excel/' . $name);
        try {
            // Vérifier si la requête contient un fichier
            if ($request->hasFile('importfile')) {
                $file = $request->file('importfile');

                // Vérifier si le fichier est un fichier Excel
                if ($file->isValid() && ($file->getClientOriginalExtension() == 'xlsx' || $file->getClientOriginalExtension() == 'xls')) {
                    // Enregistrer le fichier dans le dossier de stockage
                    $file->move(storage_path('app/excel'), $name);

                    $validateCells = new AutoEcoleCellsValidation();
                    $validateCells->validate($filePath);
                    Excel::import(new AutoEcoleImports, $filePath);
                    DB::connection("base")->commit();

                    return $this->successResponse("Importation des auto-écoles effectuée avec succès");
                } else {
                    // Si le fichier n'est pas valide, renvoyer une erreur
                    return $this->errorResponse("Le fichier n'est pas au format Excel (xlsx ou xls)");
                }
            } else {
                // Si aucun fichier n'est fourni, renvoyer une erreur
                return $this->errorResponse("Aucun fichier n'a été fourni dans la requête");
            }
        } catch (ImportationException $th) {
            unlink($filePath);
            DB::connection("base")->rollBack();
            return $this->errorResponse($th->getMessage());
        } catch (\Throwable $th) {
            unlink($filePath);
            logger()->error($th);
            DB::connection("base")->rollBack();
            return $this->errorResponse($th->getMessage());
        }
    }

    public function examinateur(Request $request)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        DB::connection("examinateur")->beginTransaction();
        DB::beginTransaction();
        $name = now()->format("d-m-Y H-s-i-") . 'examinateurs.xlsx';
        $filePath = storage_path('app/excel/' . $name);
        try {
            if ($request->hasFile('importfile')) {
                $file = $request->file('importfile');

                // Vérifier si le fichier est un fichier Excel
                if ($file->isValid() && ($file->getClientOriginalExtension() == 'xlsx' || $file->getClientOriginalExtension() == 'xls')) {
                    // Enregistrer le fichier dans le dossier de stockage
                    $file->move(storage_path('app/excel'), $name);

                    $validateCells = new ExaminateurCellsValidation();
                    $validateCells->validate($filePath);
                    Excel::import(new ExaminateurImports, $filePath);
                    DB::connection("examinateur")->commit();
                    DB::commit();

                    return $this->successResponse("Importation des examinateurs effectué avec succès");
                } else {
                    // Si le fichier n'est pas valide, renvoyer une erreur
                    return $this->errorResponse("Le fichier n'est pas au format Excel (xlsx ou xls)");
                }
            } else {
                // Si aucun fichier n'est fourni, renvoyer une erreur
                return $this->errorResponse("Aucun fichier n'a été fourni dans la requête");
            }
        } catch (ImportationException $th) {
            logger()->error($th);
            DB::connection("examinateur")->rollBack();
            DB::rollBack();
            return $this->errorResponse($th->getMessage());
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::connection("examinateur")->rollBack();
            DB::rollBack();

            return $this->errorResponse($th->getMessage());
        }
    }
}
