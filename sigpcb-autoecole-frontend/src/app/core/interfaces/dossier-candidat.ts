import { CategoryPermis } from './catgory-permis';

export interface Dossier {
  id: number;
  npi: string;
  groupage_test: string;
  group_sanguin: string;
  candidat_id: number;
  categorie_permis_id: number;
  state: string;
  created_at: string;
  updated_at: string;
  category_permis: CategoryPermis;
}

export interface Candidat {
  id: number;
  nom: string;
  prenoms: string;
  email: string;
  date_de_naissance: string;
  lieu_de_naissance: string;
  sexe: string;
  adresse: string;
  telephone: string;
  npi: string;
  created_at: string;
  updated_at: string;
}

export interface DossierSession {
  id: number;
  npi: string;
  state: string;
  type_examen: string;
  permis_extension_id: number | null;
  langue_id: number;
  auto_ecole_id: number;
  annexe_id: number;
  examen_id: number;
  dossier_candidat_id: number;
  created_at: string;
  updated_at: string;
  dossier: Dossier;
  candidat: Candidat;
  nom_permis: string;
  fiche_medical: string | null;
  canMonitored?: boolean;
  restriction_medical: string;
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

interface AutoEcole {
  id: number;
  name: string;
  promoteur_name: string;
}

interface Langue {
  id: number;
  name: string;
}

export interface DossierSession {
  id: number;
  langue_id: number;
  annexe_id: number;
  examen_id: number;
  npi: string;
  state: string;
  categorie_permis_id: number;
  auto_ecole_id: number;
  dossier_candidat_id: number;
  candidat: Candidat;
  restriction: any | null;
  dossier: Dossier;
  categorie_permis: CategoriePermis;
  annexe: Annexe;
  auto_ecole: AutoEcole;
  langue: Langue;
}
