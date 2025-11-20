<?php

namespace App\Services\Excels;

use App\Exports\StatistiqueExports;
use Maatwebsite\Excel\Facades\Excel;

class StatistiqueExcel
{
    public  function __construct(private $type)
    {
    }

    public function generate()
    {
        $format = request('format') == "csv" ? 'csv' : 'xlsx';


        $filename = "excels/statistiques.{$format}";
        Excel::store(new StatistiqueExports(), $filename);

        return route('download', ['token' => encrypt($filename)]);
    }
}
