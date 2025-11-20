import { NavItem, NavLink, NavTab } from '../interfaces/navbar-link';
import { UserAccess } from './user-access';

export class SetPermission {
  constructor(
    private readonly user: UserAccess,
    private readonly links: NavItem[]
  ) {
    this.__init__();
  }
  private __init__() {
    /**
     * Pour chaque menu du premier on vérifie
     */
    for (const lk of this.links) {
      const subLinks: NavLink[] = [];
      const subs: NavLink[] = lk.subs;

      /* Pour chaque sous-menu */
      for (const sub of subs) {
        const tabs: NavTab[] = [];
        for (const tab of sub.tabs) {
          if (this.tabHasPermission(tab)) {
            tabs.push(tab);
          }
        }
        //On recupère uniquement les onglets actifs
        sub.tabs = tabs;
        sub.show = tabs.length > 0;
        /**
         * Si le sous-menu a au moins un onglet actif
         */
        if (sub.show) {
          subLinks.push(sub);
        }
      }
      //Les sous menus actifs
      lk.subs = subLinks;

      const hasActiveSublinks = subLinks.length > 0;
      lk.tabs = lk.tabs.map((tab) => {
        tab.show = this.tabHasPermission(tab);

        return tab;
      });
      let hasTabsPermissions = lk.tabs.some((t) => t.show);
      lk.show = hasTabsPermissions || hasActiveSublinks || lk.show;
    }
  }

  private tabHasPermission(tab: NavTab) {
    if (!tab.permissions.length) {
      tab.show = true;
    } else {
      tab.show = this.user.hasPermission(tab.permissions);
    }
    return tab.show;
  }
}
