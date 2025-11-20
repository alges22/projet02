interface Candidat {
  id: number;
  nom: string;
  prenoms: string;
  email: string;
  email_verified_at: string | null;
  date_de_naissance: string;
  lieu_de_naissance: string;
  sexe: string;
  adresse: string;
  telephone: string;
  npi: string;
  created_at: string;
  updated_at: string;
}

interface DossierSession {
  restriction_medical: string;
  fiche_medical: string;
  is_militaire: string;
  categorie_permis_id: number;
  annexe_id: number;
  restriction: {
    id: number;
    name: string;
  };
}

interface Dossier {
  id: number;
  npi: string;
  groupage_test: string;
  group_sanguin: string;
  is_deleted: boolean;
  candidat_id: number;
  is_militaire: string;
  categorie_permis_id: number;
  state: string;
  created_at: string;
  updated_at: string;
}

interface CategoriePermis {
  id: number;
  name: string;
}

interface Annexe {
  id: number;
  name: string;
  adresse_annexe: string;
}

interface Chapitre {
  id: number;
  name: string;
}

interface AutoEcole {
  id: number;
  name: string;
  promoteur_name: string;
}

interface Langue {
  id: number;
  name: string;
}

export interface Suivi {
  id: number;
  auto_ecole_id: number;
  categorie_permis_id: number;
  langue_id: number;
  examen_id: number;
  annexe_id: number;
  dossier_candidat_id: number;
  dossier_session_id: number;
  chapitres_id: string;
  npi: string;
  status: boolean;
  certification: boolean;
  state: string;
  created_at: string;
  updated_at: string;
  candidat: Candidat;
  dossier_session: DossierSession;
  dossier: Dossier;
  categorie_permis: CategoriePermis;
  annexe: Annexe;
  chapitres: Chapitre[];
  auto_ecole: AutoEcole;
  langue: Langue;
}
