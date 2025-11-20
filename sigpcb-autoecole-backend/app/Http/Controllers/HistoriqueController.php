<?php

namespace App\Http\Controllers;

use App\Models\DemandeAgrementRejet;
use App\Models\DemandeLicenceRejet;
use App\Services\Help;
use App\Models\Historique;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HistoriqueController extends ApiController
{
    /**
     * Le builder pour l'historique
     *
     * @var \Illuminate\Database\Eloquent\Builder $historiques
     */
    private $historiques;
    public function __construct()
    {
        $this->historiques = Historique::query();
    }
    public function __invoke()
    {
        try {
            $filters = [];
            # On prend les historiques pour celui qui est connecté
            if (!auth()->check()) {
                return $this->errorResponse("Vous ne pouvez pas voir les notifications en tant que moniteur.");
            } else {
                $autoEcole = Help::authAutoEcole();
                if ($autoEcole) {
                    //$filters['auto_ecole_id'] = $autoEcole->id;
                }
                $filters['promoteur_id'] = auth()->id();
            }

            $this->historiques = $this->historiques
                ->where($filters)
                ->orderByDesc('created_at');

            $collection = $this->historiques
                ->get()
                ->map($this->map());
            # Groupe par service

            return $this->successResponse($collection->groupBy('service')->map(
                function (Collection $collection, $service) {
                    $reverses = $collection->reverse();
                    $first = $reverses->first();
                    return [
                        "event_at" => !is_null($first) ? $first->event_at : null,
                        'service' => $service,
                        "historiques" => $collection->values()
                    ];
                }
            )->values());
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la récupération de l'historique");
        }
    }

    private function map()
    {
        return function (Historique $historique) {

            $historique->setAttribute('event_at', Help::sessionDate($historique->created_at, 'full', true));
            $data = json_decode($historique->data, true);
            $action = $historique->action;
            $historique->makeHidden('data');
            if (is_array($data) && array_key_exists('id', $data) && array_key_exists('table', $data)) {
                $id = $data['id'];
                $table = $data['table'];

                $attrs = ['*'];

                switch ($action) {
                    case 'demande-licence-rejected':
                        $meta =  $this->getModel($id, $table, $attrs);
                        $demandeRejet = DemandeLicenceRejet::where('id', $meta->id)->where('state', 'init')->latest()->first();
                        $meta->demandeRejet = $demandeRejet;
                        break;

                    case 'demande-agrement-rejected':
                        $meta =  $this->getModel($id, $table, $attrs);
                        //information-update-rejected
                        $demandeRejet = DemandeAgrementRejet::where('id', $meta->id)->where('state', 'init')->latest()->first();
                        $meta->demandeRejet = $demandeRejet;
                        break;

                    default:
                        $meta =  $this->getModel($id, $table, $attrs);
                        break;
                }

                $historique->setAttribute('meta', $meta);
            }
            return $historique;
        };
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @param string $table
     * @param array $attrs
     * @return mixed
     */
    private function getModel($id, $table, array $attrs = ["*"])
    {
        /**
         * @var \App\Models\DemandeLicence
         */
        return  DB::table($table)->find($id, $attrs);
    }
}
