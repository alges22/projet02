import { AnnexeAnatt } from './annexe-anatt';
export interface AuthenticitePermis {
  id: number;
  email: string;
  npi: string;
  demandeur_info: any;
  num_permis: string;
  permis_file: string;
  created_at: string;
  state: string;
}
export interface DuplicataRemplacement {
  id: number;
  email: string;
  phone: string;
  npi: string;
  type: string;
  demandeur_info: any;
  annexe_info: AnnexeAnatt | null | undefined;
  annexe_id: number;
  group_sanguin: string;
  num_permis: string;
  file: string;
  created_at: string;
  state: string;
}
export interface PermisInternational {
  id: number;
  email: string;
  npi: string;
  demandeur_info: any;
  num_permis: string;
  permis_file: string;
  created_at: string;
  state: string;
  categorie_permis_ids: any;
}
export interface EchangePermis {
  id: number;
  email: string;
  npi: string;
  demandeur_info: any;
  num_permis: string;
  permis_file: string;
  created_at: string;
  state: string;
  categorie_permis_ids: any;
  delivrance_date: any;
  delivrance_ville: any;
  structure_email: any;
  group_sanguin_file: string;
  authenticite_file: string;
  group_sanguin: string;
}
export interface ProrogationPermis {
  id: number;
  email: string;
  npi: string;
  demandeur_info: any;
  num_permis: string;
  permis_file: string;
  fiche_medical_file: string;
  created_at: string;
  state: string;
  categorie_permis_ids: any;
  delivrance_date: any;
  delivrance_ville: any;
  structure_email: any;
  group_sanguin_file: string;
  group_sanguin: string;
}
