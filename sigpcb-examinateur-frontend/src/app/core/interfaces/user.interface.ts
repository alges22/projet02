export type CandidatData = {
  id: number;
  npi: string;
  auto_ecole_id: number;
  auto_ecole_name: string;
  categorie_permis_id: number;
  dossier_session: {
    id: number;
    presence_conduite: 'present' | 'absent' | null;
    resultat_conduite: null | 'success' | 'failed';
  };
  candidat: {
    avatar: string;
    prenoms: string;
    nom: string;
    sexe: 'M' | 'F';
  };
  categorie_permis: {
    id: number;
    name: string;
  };
  resultat_conduite: null | 'success' | 'failed';
};
