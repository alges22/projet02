<?php

namespace App\Imports;

use App\Services\Api;
use App\Services\Imports\Validation\AutoEcoleEntryValidation;

use Illuminate\Support\Collection;
use App\Services\Imports\FromExcelRow;
use App\Services\Imports\ExcelImportation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AutoEcoleImports implements ToCollection, WithChunkReading
{
    use Importable;

    protected $index = 0;
    /**
     * @param Collection<int, Collection> $collection
     */
    public function collection(Collection $collection)
    {
        ################################
        # Class séparée des validations
        $validator = new AutoEcoleEntryValidation();


        foreach ($collection as  $r) {
            $row = $r->toArray();
            if (!$this->isCellule($row) && $this->isNotEmpty($row)) {
                $rowInstance = new FromExcelRow($row);

                # Validation
                # Le code validation étant trop long il faut le séparer
                $validated = $validator->validate($rowInstance);

                # Importation ces données
                (new ExcelImportation($rowInstance, $validated))->create();
            }
        }
    }
    public function chunkSize(): int
    {
        return 1000;
    }

    private function isCellule(array $row)
    {
        return str($row[3])->slug() == 'commune';
    }

    /**
     * Si le l'auto-école n'est pas vide
     */
    private function isNotEmpty(array $row)
    {
        return collect($row)->some(fn ($value) => is_numeric($value) ? true : !empty($value));
    }
}