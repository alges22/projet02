<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AutoEcoleImports implements ToCollection, WithChunkReading
{
    use Importable;

    protected $index = 0;
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $this->index++;
        $path = storage_path("app/excel/chunks/{$this->index}.json");
        file_put_contents($path, $collection->toJson());
    }
    public function chunkSize(): int
    {
        return 500;
    }
}
