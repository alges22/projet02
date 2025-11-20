export interface Vague {
  status: 'new' | 'closed' | 'paused' | 'pending';
  id: string;
  numero: number;
  date_compo: string;
}
