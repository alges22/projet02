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
  permis_prealable: {
    id?: number;
    name: string;
    permis_prealable_dure?: number;
  };
  permis_prealable_dure: string;
  is_extension: boolean | number;
  extensions?: {
    categorie_permis_id: 0;
    categorie_permis_extensible_id: 0;
  }[];
}
