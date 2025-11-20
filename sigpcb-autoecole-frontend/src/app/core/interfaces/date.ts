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
  debut_etude_dossier_at: string;
  fin_etude_dossier_at: string;
  debut_gestion_rejet_at: string;
  fin_gestion_rejet_at: string;
  date_convocation: string;
  status: boolean;
  mois: string;
  annee: number;
  numero: number;
  session_long: string;
}
