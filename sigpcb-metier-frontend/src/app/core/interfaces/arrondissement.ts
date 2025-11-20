import { Commune } from "./commune";

export interface Arrondissement {
  id?: number;
  name: string;
  commune_id?: number;
  commune?: Commune;
}
