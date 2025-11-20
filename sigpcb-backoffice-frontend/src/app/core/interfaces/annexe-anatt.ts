import { Commune } from './commune';
import { Departement } from './departement';

export interface AnnexeAnatt {
  id: number;
  commune_id?: number;
  commune?: Commune;
  adresse_annexe: string;
  phone: string;
  conduite_lieu_adresse: string;
  status: boolean;
  departement_id?: number;
  departement?: Departement;
  name: string;
}
