<?php

namespace App\Exports;


use App\Services\Statistics\RapportSyntethique;
use Maatwebsite\Excel\Concerns\FromCollection;

class StatistiqueExports implements FromCollection
{
    public function collection()
    {

        $cellulesCollections = collect([
            "Candidats",
        ]);
        $listCollection = collect([]);

        $data = (new RapportSyntethique)->get(request());

        $langues = $data['langues'];
        foreach ($langues as $key => $langue) {
            $cellulesCollections->push(strtoupper($langue['name']));
        }

        $cellulesCollections->push(
            "Hommes",
            "Femmes",
            "Total",
            "Pourcentages (%)",
        );

        $list = $data['data'];

        foreach ($list as $key => $l) {
            $row = [
                strtoupper($l['name'])
            ];
            foreach ($l['langues'] as $key => $ld) {
                $row[] = $ld['count'] == "--" ? "0" : strval($ld['count']);
            }

            foreach ($l['sexes'] as $key => $sexe) {
                $row[] = $sexe['count'] == '--' ? "0" : strval($sexe['count']);
            }

            $row[] =  $l['total'] == '--' ? "0" : strval($l['total']);
            $row[] =  $l['percent'] == '--' ? "0" : $l['percent'];
            $listCollection->push($row);
        }

        return  $listCollection->prepend($cellulesCollections->all());
    }
}
