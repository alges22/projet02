export interface User {
  id: number;
  npi: string;
}

export interface Candidat {
  npi: string;
  nom: string;
  prenoms: string;
  telephone?: string;
}
