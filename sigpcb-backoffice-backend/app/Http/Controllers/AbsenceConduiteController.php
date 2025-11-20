<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Base\JuryCandidat;
use App\Models\Base\CategoriePermis;
use App\Models\Candidat\ParcoursSuivi;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Storage;
use App\Models\Candidat\DossierCandidat;
use Illuminate\Support\Facades\Validator;

class AbsenceConduiteController extends ApiController
{

    public function makeAbsence(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray(), 422);
            }

            // Rechercher la dernière insertion de la table DossierSession
            $dossierSession = DossierSession::where('npi', $request->npi)
                ->where('closed', false)
                ->latest()
                ->first();

            if (!$dossierSession) {
                return $this->errorResponse('Aucun dossier de session ouvert trouvé pour le NPI spécifié.');
            }

            // Mettre à jour le champ presence_conduite à 'absent'
            $dossierSession->update([
                'presence_conduite' => 'absent',
                'closed' => true,
                'resultat_conduite' => 'failed',
            ]);

            $dossier = DossierCandidat::find($dossierSession->dossier_candidat_id);
            if ($dossier) {
                $dossier->state = "failed";
                $dossier->save();
            }

            $categorie_permis_id = $dossier->categorie_permis_id;
            $categoriePermis = CategoriePermis::find($categorie_permis_id);
            $permisName = $categoriePermis->name;

            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $dossierSession->npi;
            $parcoursSuivi->slug = "resultat-absent-conduite";
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $dossier->candidat_id;
            $parcoursSuivi->dossier_candidat_id = $dossierSession->dossier_candidat_id;
            $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
            $parcoursSuivi->message = "Vous avez été marqué absent lors de l'épreuve de conduite. En conséquence, vous êtes recalé pour cette session. Vous avez cependant la possibilité de retenter votre chance pour cette catégorie de permis en tant que reconduit.";
            // $parcoursSuivi->bouton = json_encode(['bouton' => 'Rejet', 'status' => '1']);
            $parcoursSuivi->dossier_session_id = $dossierSession->id;
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();
            return $this->successResponse($dossierSession, 'Absence enregistrée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de l\'enregistrement de l\'absence.', 500);
        }
    }

    public function candidatSignature(Request $request)
    {
        try {
            // Valider la requête
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'jury_candidat_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray(), 422);
            }
            $juryCandidat = JuryCandidat::findOrFail($request->jury_candidat_id);
            if (!$juryCandidat) {
                return $this->errorResponse('Cette association de jurie et candidat n\'existe pas.');
            }
            // Rechercher la dernière insertion de la table DossierSession
            $dossierSession = DossierSession::find($juryCandidat->dossier_session_id);
            // Mettre à jour le champ presence_conduite à 'present'
            $dossierSession->update(['presence_conduite' => 'present']);

            // Enregistrez l'image dans le dossier public avec le nom préfixé par le NPI
            $filename = $request->jury_candidat_id . 'signed.png';
            //$this->writeSignature($request, $filename);

            // Vérifier si l'écriture du fichier s'est déroulée avec succès
            if ($filename) {
                // Mettre à jour la base de données avec le nom du fichier
                $juryCandidat = JuryCandidat::findOrFail($request->jury_candidat_id);
                $juryCandidat->update(['signature' => $filename]);

                return $this->successResponse($dossierSession, 'Présence enregistrée avec succès');
            } else {
                return $this->errorResponse('Une erreur s\'est produite lors de l\'écriture du fichier de signature.', 500);
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de l\'enregistrement de la signature.', 500);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function show(Absence $absence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Absence $absence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Absence $absence)
    {
        //
    }
}
