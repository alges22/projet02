import { CategoryPermis } from './catgory-permis';

export interface Resultat {
  date: string;
  permis: any[];
}
export interface ResultatList {
  permis: CategoryPermis;
  list: any[];
}
export interface StatCode {
  admis: number;
  recales: number;
  abscents: number;
  presentes: number;
}

export interface StatConduite {
  admis: number;
  recales: number;
  abscents: number;
  presentes: number;
}
