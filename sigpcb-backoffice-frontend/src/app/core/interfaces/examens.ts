export interface Agenda {
  id: number;
  date_code: string;
  date_conduite: string;
  debut_etude_dossier_at: string;
  fin_etude_dossier_at: string;
  debut_gestion_rejet_at: string;
  fin_gestion_rejet_at: string;
  date_convocation: string;
  session: string | null;
  session_long: string | null;
  code_state: 'init' | 'pending' | 'closed' | null;
  conduite_state: 'init' | 'pending' | 'closed' | null;
  status: boolean;
  type: string;
  name: string;
  closed: boolean;
  mois: string;
  annee: number;
  numero: number;
  created_at: string;
  updated_at: string;
  programs?: any[];
  annexe_ids: number[];
}
