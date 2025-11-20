<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use App\Services\Help;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string|string $code_state (init, pending, closed)
 * @property string|string $name Le nom de la session
 * @property string|string $session_long Le nom complet de la session
 * @property Carbon $date_code (init, pending, closed)
 * @property Carbon $debut_etude_dossier_at
 * @property Carbon $fin_etude_dossier_at
 * @property Carbon $date_conduite
 * @property string $conduite_state
 * @property string $debut_gestion_rejet_at
 * @property Carbon $date_convocation
 * @property string $convocation_state
 * @property string $fin_gestion_rejet_at
 * @property bool $opened
 * @property bool $closed
 * @property int $id
 *
 */
class Examen extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'debut_etude_dossier_at', 'fin_etude_dossier_at', 'debut_gestion_rejet_at', 'fin_gestion_rejet_at', 'date_code', 'annee', 'numero', 'date_conduite', 'date_convocation', 'status', 'mois', 'annexe_ids'];

    protected $casts = [
        "debut_etude_dossier_at" => "datetime",
        "fin_etude_dossier_at" => "datetime",
        "debut_gestion_rejet_at" => "datetime",
        "fin_gestion_rejet_at" => "datetime",
        "date_code" => "datetime",
        "date_conduite" => "datetime",
        "date_convocation" => "datetime",
        "closed" => "boolean",
        "annexe_ids" => "array",

    ];

    /**
     * Retourne le premier examen dont la date de conduite n'est pas encore passée
     *
     * @return $this|null
     */
    public static function recent(array $attr = ['*'])
    {
        return static::latest('date_conduite')->first($attr);
    }

    /**
     * Si la date est antérieure aujourd'hui, state = init
     * Si la date est entre debut et fin  state = pending
     * Si la date est  post à fin alors state = closed
     * @return $this
     */
    public function withEtudeDossierState()
    {
        $this->setAttribute('etude_dossier', 'pending');

        if ($this->debut_etude_dossier_at->isFuture()) {
            $this->setAttribute('etude_dossier', 'init');
        } elseif ($this->debut_etude_dossier_at->isBefore($this->fin_etude_dossier_at)) {
            $this->setAttribute('etude_dossier', 'pending');
        } else {
            $this->setAttribute('etude_dossier', 'closed');
        }

        return $this;
    }

    public function withGestionDossierState()
    {
        $this->setAttribute('gestion_dossier', 'pending');
        return $this;
    }

    /**
     * Etat actuel de la date code
     *
     * @return $this
     */
    public function withDateCode()
    {
        if ($this->date_code->isFuture()) {
            $this->setAttribute('code_state', 'init');
        } else {
            $this->setAttribute('code_state', 'pending');
        }
        return $this;
    }


    /**
     * Etat actuel de la date de convocation
     *
     * @return $this
     */
    public function withConvocationDate()
    {

        if ($this->date_convocation->isFuture()) {
            // Si la date de convocation n'est pas encore arrivée
            $this->setAttribute('convocation_state', 'init');
        } else {
            // Si la date de convocation est arrivée et après la date de composition
            $this->setAttribute('convocation_state', 'pending');
        }

        return $this;
    }


    /**
     * Vérifier si on est au jour de conduite
     * Si on est entre 8h00 et 19h00, conduite_state = pending
     * Si on est avant 8h00, conduite_state = init,
     * Si on est avant 19h00, conduite_state = closed
     *
     * @return $this
     */
    /**
     * Etat actuel de la date de conduite
     *
     * @return $this
     */
    public function withDateConduite()
    {
        $now = Carbon::now();

        if ($this->date_conduite->isFuture()) {
            // Si la date de conduite est dans le futur
            $this->setAttribute('conduite_state', 'init');
        } else {
            $this->setAttribute('conduite_state', 'pending');
        }

        return $this;
    }


    public function withIsOpened()
    {
        $this->setAttribute('opened', !$this->closed);
        return $this;
    }

    public function withPrograms()
    {
        $attrs = [
            "debut_etude_dossier_at" => [
                'label' => "Etude dossiers",
                'color' => "#00A884",
                'end' => false,
                'details' => null
            ],

            "debut_gestion_rejet_at" => [
                'label' => "Gestion rejets",
                'color' => "#00A884",
                'end' => false,
                'details' => null
            ],

            "date_convocation" => [
                'label' => "Convocation",
                'color' => "orange",
                'end' => false,
                'details' => null
            ],
            "date_code" => [
                'label' => "Composition (code)",
                'color' => "#0164BC",
                'end' => false,
                'details' => 'Composition (code)'
            ],

            "date_conduite" => [
                'label' => "Composition (conduite)",
                'color' => "#0164BC",
                'end' => true,
                'details' => 'Composition (conduite)'
            ],
        ];

        $dateData = [];

        foreach ($attrs as $attr => $data) {
            $dateInstance = Carbon::parse($this->{$attr});
            $data['month'] = ucfirst($dateInstance->monthName);
            $data['days'] = $dateInstance->day;
            $data['id'] = $this->id;
            $dateData[] = $data;
        }

        $this->setAttribute('programs', $dateData);

        return $this;
    }

    // public function asAgenda()
    // {
    //     $this->withEtudeDossierState();
    //     $this->withGestionDossierState();
    //     $this->withConvocationDate();
    //     $this->withDateCode();
    //     $this->withDateConduite();
    //     $this->withPrograms();
    //     $this->withIsOpened();
    //     $this->withSessionName();
    //     return $this;
    // }
    public function asAgenda()
    {
        // Enrichir les différents états de l'examen
        $this->withEtudeDossierState();
        $this->withGestionDossierState();
        $this->withConvocationDate();
        $this->withDateCode();
        $this->withDateConduite();
        $this->withPrograms();
        $this->withIsOpened();
        $this->withSessionName();

        // Ajouter les annexes en fonction des IDs dans 'annexe_ids'
        $annexes = $this->getAnnexesByIds($this->annexe_ids);
        $this->setAttribute('annexes', $annexes);

        return $this;
    }

    public function getAnnexesByIds($annexeIds)
    {
        // Si `annexe_ids` est un tableau JSON (par exemple [9, 8])
        if (is_array($annexeIds) && count($annexeIds) > 0) {
            // Récupérer les annexes dans la base de données par leurs IDs
            return AnnexeAnatt::whereIn('id', $annexeIds)->get();
        }

        // Retourner une collection vide si aucun ID d'annexe n'est fourni
        return collect();
    }

    public function withSessionName()
    {
        $sessionInstance = Carbon::parse($this->date_code);
        $this->setAttribute('session', Help::sessionDate($sessionInstance));
        $this->setAttribute('session_date',  Help::sessionDate($sessionInstance, "full"));
    }

    public function isClosed()
    {
        return $this->closed;
    }



    public function dossierSessions()
    {
        return $this->hasMany(DossierSession::class, 'examen_id');
    }
    public function scopeFilter(Builder $query, array $filters)
    {

        $month = intval(data_get($filters, "month"));
        $query->when($month, function (Builder $query) use ($month) {
            return $query->whereMonth('date_code', $month);
        });

        $year = intval(data_get($filters, "year"));
        $query->when($year, function (Builder $query) use ($year) {
            return $query->whereYear('date_code', $year);
        });

        $type = data_get($filters, "type");
        $query->when(in_array($type, ['ordinaire', 'extra', 'militaire']), function (Builder $query) use ($type) {
            return $query->where('type', $type);
        });

        $types = data_get($filters, "types");
        if ($types) {
            $types = explode(",", $types);
            $query->when($types, function (Builder $query) use ($types) {
                return $query->whereIn('type', $types);
            });
        }


        $query->when(array_key_exists("status", $filters), function (Builder $query) use ($filters) {
            return $query->where('status', boolval($filters['status']));
        });

        $query->when(array_key_exists("closed", $filters), function (Builder $query) use ($filters) {
            return $query->where('closed', boolval($filters['closed']));
        });

        $search = data_get($filters, "search");
        $query->when($search, function (Builder $query) use ($search) {
            return $query->where('name', 'LIKE', "%$search%");
        });

        $date_code = data_get($filters, "date_code");
        if ($date_code) {
            $query->when(Carbon::parse($date_code)->isValid(), function (Builder $query) use ($date_code) {
                return $query->whereDate('date_code', $date_code);
            });
        }


        return $query;
    }
}
