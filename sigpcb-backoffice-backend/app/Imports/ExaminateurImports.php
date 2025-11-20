<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use App\Services\Imports\FromExaminateurRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Services\Imports\ExaminateurExcelImportation;
use App\Services\Imports\Validation\AutoEcoleEntryValidation;
use App\Services\Imports\Validation\ExaminateurEntryValidation;

class ExaminateurImports implements ToCollection, WithChunkReading
{
    use Importable;


    /**
     * @param Collection $collection
     */
    // public function collection(Collection $collection)
    // {

    //     ################################
    //     # Class séparée des validations
    //     $validator = new AutoEcoleEntryValidation();
    //     //Pour chaque ligne d l'excel
    //     foreach ($collection as  $r) {
    //         $row = $r->toArray();
    //         if (!$this->isCellule($row) && $this->isNotEmpty($row)) {
    //             try {
    //                 $rowInstance = new FromExaminateurRow($row);
    //                 (new ExaminateurExcelImportation($rowInstance))->create();
    //             } catch (\Throwable $th) {
    //                 throw $th;
    //             }
    //         }
    //     }
    // }
    public function collection(Collection $collection)
    {
        ################################
        # Class séparée des validations
        $validator = new ExaminateurEntryValidation();


        foreach ($collection as  $r) {
            $row = $r->toArray();
            if (!$this->isCellule($row) && $this->isNotEmpty($row)) {
                $rowInstance = new FromExaminateurRow($row);

                # Validation
                # Le code validation étant trop long il faut le séparer
                $validated = $validator->validate($rowInstance);

                # Importation ces données
                (new ExaminateurExcelImportation($rowInstance, $validated))->create();
            }
        }
    }
    public function chunkSize(): int
    {
        return 1000;
    }

    private function isCellule(array $row)
    {
        return str($row[1])->slug() == 'npi';
    }

    /**
     * Si le NPI n'est pas vide
     */
    private function isNotEmpty(array $row)
    {
        return !is_null($row[1]);
    }
}
