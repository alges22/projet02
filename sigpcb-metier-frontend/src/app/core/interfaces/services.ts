export interface AuthenticitePermis {
  id: number;
  email: string;
  npi: string;
  infos: any;
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
  infos: any;
  annexe_id: number;
  group_sanguin: string;
  num_permis: string;
  file: string;
  created_at: string;
  state: string;
}

export interface EserviceParcours {
  id: number;
  service: string;
  candidat_id: number;
  auto_ecole_id: number | null;
  agent_id: number | null;
  candidat_justif_absence_id: number | null;
  categorie_permis_id: number | null;
  npi: string;
  slug: string;
  message: string;
  bouton: string | null;
  model_info: any | null | undefined;
  action: string | null;
  url: string;
  data: any;
  date_action: string;
  dossier_candidat_id: number | null;
  dossier_session_id: number | null;
  created_at: string;
  updated_at: string;
}
