import { Departement } from "./departement";

export interface Commune {
  id?: number;
  name: string;
  departement_id?: number;
  departement?: Departement;
}
