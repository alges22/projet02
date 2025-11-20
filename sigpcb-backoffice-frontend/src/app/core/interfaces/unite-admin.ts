export interface UniteAdmin {
  id?: number;
  name?: string;
  ua_parent_id?: string | null;
  status?: boolean;
  sigle?: string;
  parent?: UniteAdmin;
}
