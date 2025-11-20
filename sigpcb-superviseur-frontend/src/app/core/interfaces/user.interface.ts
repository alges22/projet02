export interface TempAutoEcole {
  id: number;
  numero_autorisation: string | null;
  name: string;
  is_verify: boolean;
}
export interface AutoEcole extends TempAutoEcole {
  promoteur_name: string;

  email: string;
  adresse: string | null;
  annee_creation: number | null;
  commune_id: number | null;
  cpu_accepted: boolean;
  created_at: string;
  departement_id: number | null;
  departement_name: string | null;
  email_verified_at: string | null;
  fichier_ifu: string | null;
  fichier_rccm: string | null;
  num_ifu: string;
  phone: string;
  promoteur_phone: string | null;
  status: boolean;
  updated_at: string;
}

export type CandidatData = {
  id: number;
  npi: string;
  presence: 'present' | 'abscent';
  closed: boolean;
  questions_count: number;
  connected: boolean;
  num_table: number;
  reponses_count: number;

  salle_compo_id: string | number;
  examen_id: string | number;
  candidat: {
    avatar: string;
    prenoms: string;
    nom: string;
    sexe: 'M' | 'F';
  };
};
