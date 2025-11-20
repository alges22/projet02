export type ExportType = 'pdf' | 'excel' | 'csv';

export type DispensePaiement = {
  id: number;
  created_at: number;
  validated_at: number | null;
  validator_npi: string | null;
  validator_id: number | null;
  status: 'init' | 'used' | 'validated' | 'rejected';
  used_at: string | null;
  examen_id: number | null;
  candidat_npi: number | null;
  dossier_session_id: number | null;
  note?: string;
  candidat_info: {
    nom: string;
    prenoms: string;
  };
};
