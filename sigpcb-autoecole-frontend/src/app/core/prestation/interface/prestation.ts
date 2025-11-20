export interface Prestation {
  title: string;
  image: string;
  slug: string;
  href: string;
  actionText: string;
  users: ('moniteur' | 'promoteur')[];
}
