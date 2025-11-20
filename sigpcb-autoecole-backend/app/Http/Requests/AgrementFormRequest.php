<?php

namespace App\Http\Requests;

use App\Models\AutoEcole;
use App\Models\Commune;
use Illuminate\Support\Str;
use App\Rules\StartWithLetter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AgrementFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'npi' => 'required|digits:10',
            'auto_ecole' => ['required', 'min:2','max:80', function ($attr, $value, $fail) {
                $slug = Str::slug($value);
                if (AutoEcole::where('slug', $slug)->orWhere("name", trim($value))->exists()) {
                    $fail("Le nom d'auto-école est déjà pris.");
                }
            }],
            'ifu' => 'required',
            'departement_id' => 'required|exists:departements,id',
            'commune_id' => ['required', function ($attribute, $value, $fail) {
                $commune = Commune::find($value);
                if (!$commune) {
                    return  $fail('La commune est obligatoire.');
                }
                if ($commune->departement_id != $this->input("departement_id")) {
                    $fail('La commune sélectionnée ne semble pas appartenir au département sélectionné');
                }
            }],
            'moniteurs' => 'required|array|min:2|max:10',
            'moniteurs.*' => 'digits:10|distinct:strict',
            'telephone_pro' => 'required|string|min:4|max:21',
            'email_pro' => 'required|email|max:64|unique:auto_ecoles,email',
            'email_promoteur' => 'required|email|max:64',
            'vehicules' => 'required|array|min:1|max:10',
            'vehicules.*' => 'string|min:4|distinct:ignore_case',
            'nat_promoteur' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'casier_promoteur' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'copie_statut' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'reg_commerce' => 'required|file|mimes:pdf|max:2048',
            'ref_promoteur' => 'required|file|mimes:pdf|max:2048',
            'descriptive_locaux' => 'required|file|max:2048',
            'attest_fiscale' => 'required|file|mimes:pdf|max:2048',
            'attest_reg_organismes' => 'required|file|mimes:pdf|max:2048',
            'carte_grise' => 'required|array',
            'assurance_visite' => 'required|array',
            'photo_vehicules' => 'required|array',
            "carte_grise.*" => "required|file|mimes:pdf,jpg,png|max:2048",
            "assurance_visite.*" => "required|file|mimes:pdf,jpg,png|max:2048",
            "photo_vehicules.*" => "required|file|mimes:pdf,jpg,png|max:2048",
        ];
    }

    public function messages()
    {
        return [
            'npi.required' => 'Le NPI est obligatoire.',
            'npi.digits' => 'Le NPI doit contenir exactement 10 chiffres.',
            'npi.numeric' => 'Le NPI ne doit contenir que des chiffres.',
            'npi.size' => 'Le NPI doit contenir exactement 10 chiffres.',

            'auto_ecole.required' => 'Le nom de l\'auto-école est obligatoire.',
            'auto_ecole.string' => 'Le nom de l\'auto-école doit être une chaîne de caractères.',
            'auto_ecole.min' => 'Le nom de l\'auto-école doit contenir au moins 2 caractères.',
            'ifu.required' => 'L\'IFU est obligatoire.',
            'ifu.numeric' => 'L\'IFU ne doit contenir que des chiffres.',
            'departement_id.required' => 'Le département est obligatoire.',
            'departement_id.exists' => 'Le département est obligatoire.',
            'commune_id.required' => 'La commune est obligatoire.',
            'commune_id.exists' => 'La commune sélectionnée n\'existe pas.',
            'moniteurs.required' => 'Les moniteurs sont requis.',
            'moniteurs.array' => 'Le champ Moniteurs doit être un tableau.',
            'moniteurs.min' => 'Veuillez indiquez au moins :min moniteurs.',
            'moniteurs.*.digits' => 'Veuillez vérifier les numeros NPIs des moniteurs.',
            'moniteurs.*.distinct' => 'Veuillez sélectionner des NPIs différents.',
            'telephone_pro.required' => 'Le champ Téléphone professionnel est requis.',
            'telephone_pro.string' => 'Le champ Téléphone professionnel doit être une chaîne de caractères.',
            'telephone_pro.min' => 'Le champ Téléphone professionnel doit avoir au moins :min caractères.',
            'email_pro.required' => 'Le champ Email professionnel est requis.',
            'email_pro.email' => 'Le champ Email professionnel doit être une adresse email valide.',
            'email_pro.unique' => 'L\' Email professionnel est déjà pris.',
            'email_promoteur.required' => 'Le champ Email du promoteur est requis.',
            'email_promoteur.email' => 'Le champ Email du promoteur doit être une adresse email valide.',
            'vehicules.required' => 'Le champ Véhicules est requis.',
            'vehicules.array' => 'Le champ Véhicules doit être un tableau.',
            'vehicules.min' => 'Le champ Véhicules doit avoir au moins :min élément.',
            'vehicules.*.string' => 'Chaque élément du champ Véhicules doit être une chaîne de caractères.',
            'vehicules.*.min' => 'Chaque élément du champ Véhicules doit avoir au moins :min caractères.',
            'nat_promoteur.required' => 'Le champ Nationalité du promoteur est requis.',
            'nat_promoteur.image' => 'Le champ Nationalité du promoteur doit être une image.',
            'nat_promoteur.max' => 'Le champ Nationalité du promoteur ne doit pas dépasser :max kilo-octets.',

            'casier_promoteur.required' => 'Le champ Casier judiciaire du promoteur est requis.',
            'casier_promoteur.image' => 'Le champ Casier judiciaire du promoteur doit être une image.',
            'casier_promoteur.max' => 'Le champ Casier judiciaire du promoteur ne doit pas dépasser :max kilo-octets.',

            'copie_statut.file' => 'Le champ Copie du statut doit être un fichier.',
            'copie_statut.mimes' => 'Le champ Copie du statut doit être un fichier de type :values.',
            'copie_statut.max' => 'Le champ Copie du statut ne doit pas dépasser :max kilo-octets.',

            'reg_commerce.required' => 'Le champ Registre de commerce est requis.',
            'reg_commerce.file' => 'Le champ Registre de commerce doit être un fichier.',
            'reg_commerce.mimes' => 'Le champ Registre de commerce doit être un fichier de type :values.',
            'reg_commerce.max' => 'Le champ Registre de commerce ne doit pas dépasser :max kilo-octets.',

            'ref_promoteur.required' => 'Le champ Référence du promoteur est requis.',
            'ref_promoteur.file' => 'Le champ Référence du promoteur doit être un fichier.',
            'ref_promoteur.mimes' => 'Le champ Référence du promoteur doit être un fichier de type :values.',
            'ref_promoteur.max' => 'Le champ Référence du promoteur ne doit pas dépasser :max kilo-octets.',

            'descriptive_locaux.required' => 'Le champ Description des locaux est requis.',
            'descriptive_locaux.file' => 'Le champ Description des locaux doit être un fichier.',
            'descriptive_locaux.max' => 'Le champ Description des locaux ne doit pas dépasser :max kilo-octets.',
            'attest_fiscale.required' => 'Le champ Attestation fiscale est requis.',
            'attest_fiscale.file' => 'Le champ Attestation fiscale doit être un fichier.',
            'attest_fiscale.mimes' => 'Le champ Attestation fiscale doit être un fichier de type :values.',
            'attest_fiscale.max' => 'Le champ Attestation fiscale ne doit pas dépasser :max kilo-octets.',

            'attest_reg_organismes.required' => 'Le champ Attestation des organismes est requis.',
            'attest_reg_organismes.file' => 'Le champ Attestation des organismes doit être un fichier.',
            'attest_reg_organismes.mimes' => 'Le champ Attestation des organismes doit être un fichier de type :values.',
            'attest_reg_organismes.max' => 'Le champ Attestation des organismes ne doit pas dépasser :max kilo-octets.',

            'carte_grise.*.required' => 'Le champ Carte grise est requis.',
            'carte_grise.*.file' => 'Le champ Carte grise doit être un fichier.',
            'carte_grise.*.mimes' => 'Le champ Carte grise doit être un fichier de type :values.',

            'assurance_visite.*.required' => 'Le champ Assurance de visite est requis.',
            'assurance_visite.*.file' => 'Le champ Assurance de visite doit être un fichier.',
            'assurance_visite.*.mimes' => 'Le champ Assurance de visite doit être un fichier de type :values.',

            'photo_vehicules.*.required' => 'Le champ Photo des véhicules est requis.',
            'photo_vehicules.*.file' => 'Le champ Photo des véhicules doit être un fichier.',
            'photo_vehicules.*.mimes' => 'Le champ Photo des véhicules doit être un fichier de type :values.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw (new ValidationException($validator));
    }
}
