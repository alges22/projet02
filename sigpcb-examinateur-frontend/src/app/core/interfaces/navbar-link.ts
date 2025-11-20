export interface NavbarLink {
  label: string;
  icon: string;
  href: string;
  navid: string;
  active?: boolean;
  children?: this[];
  prevent?: boolean;
  open?: boolean;
}
