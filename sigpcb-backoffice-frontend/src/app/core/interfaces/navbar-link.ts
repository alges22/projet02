import { PermissionExpression } from './role';

export interface NavTab {
  label: string;
  data: string;
  href: string;
  active: boolean;
  icon: string;
  show: boolean;
  permissions: PermissionExpression[];
  anyPermissions?: PermissionExpression[];
}
export interface NavLink {
  label: string;
  href: string;
  active: boolean;
  show: boolean;
  tabs: NavTab[];
}
export interface NavItem {
  icon: string;
  label: string;
  href: string;
  open: boolean;
  active: boolean;
  show: boolean;
  tabs: NavTab[];
  subs: NavLink[];
}
