<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\Help;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string|string $code_state (init, pending, closed)
 * @property Carbon $date_code (init, pending, closed)
 * @property string $debut_etude_dossier_at
 * @property string $fin_etude_dossier_at
 * @property string $date_conduite
 * @property string $conduite_state
 * @property string $debut_gestion_rejet_at
 * @property string $date_convocation
 * @property string $convocation_state
 * @property string $fin_gestion_rejet_at
 * @property bool $opened
 * @property int $id
 *
 */
class Examen extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'debut_etude_dossier_at', 'fin_etude_dossier_at', 'debut_gestion_rejet_at', 'fin_gestion_rejet_at', 'date_code', 'annee', 'numero', 'date_conduite', 'date_convocation', 'status', 'mois'];

    protected $casts = [
        "debut_etude_dossier_at" => "datetime",
        "fin_etude_dossier_at" => "datetime",
        "debut_gestion_rejet_at" => "datetime",
        "fin_gestion_rejet_at" => "datetime",
        "date_code" => "datetime",
        "date_conduite" => "datetime",
        "date_convocation" => "datetime"
    ];
    protected $table = "examens";

    protected $connection = "base";
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
        $now = Carbon::now();

        if ($now->lt($this->date_code)) {
            $this->setAttribute('code_state', 'init');
        } else {
            if ($now->lt($this->conduite_state)) {
                $this->setAttribute('code_state', 'pending');
            } else {
                $this->setAttribute('code_state', 'closed');
            }
        }


        return $this;
    }

    /**
     * Vérifier l'état de la convocation
     * Si on est avant 8h00 le jour de la convocation, convocation_state = init
     * Si on est entre 8h00 et 19h00 le jour de la convocation, convocation_state = pending
     * Si on est après 19h00 le jour de la convocation, convocation_state = closed
     *
     * @return $this
     */
    public function withConvocationDate()
    {
        $now = Carbon::now();
        $this->setAttribute('convocation_state', 'pending');

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
    public function withDateConduite()
    {
        $this->setAttribute('conduite_state', 'pending');
        return $this;
    }

    public function withIsOpened()
    {
        $this->withDateConduite();
        $this->setAttribute('opened', $this->conduite_state !== 'closed');
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

    public function asAgenda()
    {
        $this->withEtudeDossierState();
        $this->withGestionDossierState();
        $this->withConvocationDate();
        $this->withDateCode();
        $this->withDateConduite();
        $this->withPrograms();
        $this->withIsOpened();
        $this->withSessionName();
        return $this;
    }

    public function withSessionName()
    {
        $sessionInstance = Carbon::parse($this->date_code);
        $this->setAttribute('session', Help::sessionDate($sessionInstance));
        $this->setAttribute('session_date',  Help::sessionDate($sessionInstance, "full"));
    }
}