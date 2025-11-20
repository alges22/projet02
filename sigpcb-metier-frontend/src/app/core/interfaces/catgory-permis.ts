import { TrancheAge } from './tranche-age';

export interface CategoryPermis {
  id: number;
  name: string;
  status: boolean;
  validite: number;
  age_min: number;
  is_valid_age: boolean;
  montant: number;
  montant_militaire: number;
  montant_etranger: number;
  note_min: number;
  description: string;
  permis_prealable: any;
  permis_prealable_dure: string;
  is_extension: boolean | number;
  trancheage?: TrancheAge[];
  extensions?: any;
}
