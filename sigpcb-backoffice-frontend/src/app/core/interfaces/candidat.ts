export interface DossierCandidat {
  id: number;
  candidat_id: number;
  adresse: null;
  arrondissement_id: null;
  auto_ecole_id: number;
  candidat: {
    id: string;
    nom: string;
    prenoms: string;
    email: string;
  };
  categorie_permis_id: number;
  code_autoecole: string;
  created_at: string;
  date_payment: string | null;
  date_soumission: string;
  date_validation: string | null;
  examen_id: null;
  fiche_medical: string;
  fichier_acte_nais: null;
  fichier_permis_prealable: null;
  fichier_piece_identite: null;
  fichier_visite_med: null;
  group_sanguin: string;
  groupage_test: string;
  hopital: null;
  is_deleted: boolean;
  is_militaire: string;
  is_paid: boolean;
  is_valid: boolean;
  langue_id: number;
  medecin1_contact: null;
  medecin1_name: null;
  medecin2_contact: null;
  medecin2_name: null;
  npi: string;
  num_matricule: null;
  num_permis: null;
  num_piece_identite: null;
  permis_extension_id: number;
  permis_prealable_id: null;
  phone: null;
  photo: null;
  restriction_medical: string;
  resultat_conduite: null;
  type_piece_id: null;
  updated_at: string;
}

export interface Monitoring {
  categorie_permis_id: number;
  langue_id: number;
  npi: string[];
  dossier_candidat_id: number[];
  chapitres_id: number[];
  status: boolean;
  certification: boolean;
}

export interface Dossier {
  id: number;
  npi: string;
  groupage_test: string;
  group_sanguin: string;
  is_deleted: boolean;
  candidat_id: number;
  is_militaire: null | boolean;
  categorie_permis_id: number;
  state: 'pending' | 'approved' | 'rejected'; // You can adjust the possible states as needed
  created_at: string;
  updated_at: string;
  ancien_permis?: {
    id: number;
    num_matricule: string;
    num_permis: string;
    fichier_permis_prealable: string;
    categorie_permis_id: number;
    candidat_id: number;
    dossier_candidat_id: number;
    created_at: string;
    updated_at: string;
  } | null;
}
