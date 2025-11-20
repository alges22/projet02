import { Titre } from './titre';
import { UniteAdmin } from './unite-admin';
export interface User {
  id?: number;
  last_name: string;
  first_name: string;
  email: string;
  password?: string;
  status: boolean;
  unite_admin_id?: number;
  phone?: string;
  profil_ids?: string;
  unite_admin?: UniteAdmin;
  titre_id?: number;
  titre?: Titre;
  roles?: any;
  role_id: number;
  npi: string;
}
