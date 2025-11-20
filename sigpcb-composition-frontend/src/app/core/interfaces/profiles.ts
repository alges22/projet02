interface Examen {
  id: number;
  date_compo: string;
  date_de_conduite: string;
}

interface Langue {
  id: number;
  name: string;
}

interface Annexe {
  id: number;
  name: string;
  conduite_lieu_adresse: string;
}

interface Salle {
  id: number;
  name: string;
}

interface AutoEcole {
  id: number;
  name: string;
}

interface Vague {
  id: number;
  date_compo_complet: string;
}

export interface ProfileData {
  access_token: string | null;
  candidat: any;
  examen: Examen;
  langue: Langue;
  annexe: Annexe;
  salle: Salle;
  vague: Vague;
  auto_ecole: AutoEcole;
  lostConnection: boolean;
  page: any;
  questionCount: number;
  categorie_permis: {
    id: number;
    name: string;
  };
}
