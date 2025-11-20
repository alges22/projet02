export interface TempAutoEcole {
  id: number;
  numero_autorisation: string | null;
  name: string;
  is_verify: boolean;
}
export interface AutoEcole extends TempAutoEcole {
  promoteur_name: string;
  moniteurs: Moniteur[];
  email: string;
  adresse: string | null;
  annee_creation: number | null;
  commune_id: number | null;
  cpu_accepted: boolean;
  created_at: string;
  departement_id: number | null;
  departement_name: string | null;
  email_verified_at: string | null;
  fichier_ifu: string | null;
  fichier_rccm: string | null;
  num_ifu: string;
  phone: string;
  promoteur_phone: string | null;
  status: boolean;
  updated_at: string;
  licence: any;
  agrement: any;
  annexe: {
    name: string;
    email?: string | null;
    phone?: string | null;
  };
  code: string;
}

export interface OTPEvent {
  isValid: boolean;
  values: (number | null)[];
}

export interface Promoteur {
  id: number;
  nom: string;
  prenoms: string;
  email: string;
  date_de_naissance: string;
  lieu_de_naissance: string;
  sexe: string;
  adresse: string;
  telephone: string;
  avatar: string;
  npi: string;
  type?: 'promoteur' | 'moniteur';
  created_at: string;
  updated_at: string;
}

export interface DemandeAgrementFile {
  id: number;
  demande_agrement_id: number;
  nat_promoteur: string;
  casier_promoteur: string;
  ref_promoteur: string;
  reg_commerce: string;
  attest_fiscale: string;
  attest_reg_organismes: string;
  descriptive_locaux: string;
  permis_moniteurs: string;
  copie_statut: string | null;
  created_at: string; // Format à ajuster selon vos besoins
  updated_at: string; // Format à ajuster selon vos besoins
}

export interface Moniteur extends Promoteur {
  type?: 'promoteur' | 'moniteur';
}

export interface Vehicule {
  id: number;
  immatriculation: string;
}

export interface DemandeAgrement {
  id: number;
  state: string;
  auto_ecole: string;
  ifu: string;
  departement_id: number;
  commune_id: number;
  quartier?: string | null;
  ilot?: string | null;
  parcelle?: string | null;
  moniteurs: any;
  vehicules: any;
  telephone_pro: string;
  email_pro: string;
  fiche: DemandeAgrementFile | null;
  email_promoteur: string;
}

export interface Fiche {
  id: string;
  label: string;
  accept: string[]; // ex: ['.jpg', '.png']
  file: File | Blob | null | undefined | File[];
  type: 'pdf' | 'file' | 'img' | 'word';
  placeholder?: string;
  required: boolean;
  name: string;
  defaultPath: string[];
  multiple?: boolean;
}

export interface Ae {
  codeLicence: string;
  codeAgrement: string;
  auto_ecole_id: string | number;
  endLicence: string;
  name: string;
  annexe: any;
}
export type NotificationAction =
  | 'demande-licence-rejected'
  | 'demande-licence-validate'
  | 'demande-licence-pending'
  | 'paiement-approved'
  | 'demande-agrement-validate'
  | 'demande-agrement-rejected'
  | 'demande-agrement-pending'
  | 'information-update-validate'
  | 'information-update-rejected';

export interface Notification {
  id: number;
  service: 'agrement' | 'license';
  action: NotificationAction;
  title: string;
  npi: string;
  message: string;
  bouton: string;
  promoteur_id: number;
  created_at: string;
  updated_at: string;
  event_at: string;
  meta: any;
}
