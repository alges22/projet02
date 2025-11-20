import { DossierCandidat } from './candidat';

export interface Examinateur {
  id: number;
  email: string;
  npi: string;
  demandeur_info: any;
  annexe_anatt_info: any;
  num_permis: string;
  permis_file: string;
  created_at: string;
  state: string;
  categorie_permis_ids: any;
}

export interface RecrutementEntreprise {
  id: number;
  name: string;
  phone: string;
  email: string;
}

export interface RecrutementCandidat {
  id: number;
  npi: string;
  num_permis: string;
  permis_file: string;
  langue: string;
  recrutement_id: number;
  entreprise_id: number;
  state: 'init' | 'pending' | 'validate' | 'rejected';
  candidat_info: {
    id: string;
    nom: string;
    prenoms: string;
    email: string;
    sexe: string;
  } | null;
}

export interface RecrutementDemande {
  id: number;
  entreprise_id: number;
  entreprise: RecrutementEntreprise | null;
  categorie_permis_id: string;
  annexe_id: string;
  date_compo: string;
  finished: boolean;
  state: 'init' | 'pending' | 'validate' | 'rejected';
  date_validation: string | null;
  date_rejet: string | null;
  created_at: string;
  updated_at: string;
  categorie_permis: {
    id: number;
    name: string;
    description: string | null;
    status: boolean;
    age_min: number;
    is_valid_age: boolean;
    montant_militaire: number;
    montant_etranger: number;
    montant: number;
    note_min: number;
    permis_prealable: string | null;
    permis_prealable_dure: string | null;
    is_extension: boolean;
    created_at: string;
    updated_at: string;
  };
  annexe: {
    id: number;
    name: string;
    adresse_annexe: string;
    phone: string;
    conduite_lieu_adresse: string | null;
    commune_id: number;
    departement_id: number | null;
    status: boolean;
    created_at: string | null;
    updated_at: string | null;
  };
}

export interface Moniteur {
  id: number;
  email: string;
  npi: string;
  demandeur_info: any;
  annexe_anatt_info: any;
  num_permis: string;
  permis_file: string;
  diplome_file: string;
  created_at: string;
  state: string;
  categorie_permis_ids: any;
}
