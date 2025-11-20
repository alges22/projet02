export type FullMonth =
  | 'Janvier'
  | 'Février'
  | 'Mars'
  | 'Avril'
  | 'Mai'
  | 'Juin'
  | 'Juillet'
  | 'Août'
  | 'Septembre'
  | 'Octobre'
  | 'Novembre'
  | 'Décembre';

export type FullDay =
  | 'Lundi'
  | 'Mardi'
  | 'Mercredi'
  | 'Jeudi'
  | 'Vendredi'
  | 'Samedi'
  | 'Dimanche';

export interface Agenda {
  id: number;
  date_code: string;
  date_conduite: string;
  date_session_open: string;
  date_etude_dossier: string;
  date_gestion_rejet: string;
  date_convocation: string;
  status: boolean;
  mois: string;
  annee: number;
  numero: number;
  created_at: string;
  updated_at: string;
}
