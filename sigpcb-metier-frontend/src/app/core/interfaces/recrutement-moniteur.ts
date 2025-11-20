export interface RecrutementMoniteurParcours {
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
