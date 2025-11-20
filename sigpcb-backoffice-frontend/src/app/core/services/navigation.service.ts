import { Injectable } from '@angular/core';
import { NavItem, NavTab } from '../interfaces/navbar-link';
import { ROUTES } from '../routes/full';
import { UserAccess } from '../model/user-access';
import { SetPermission } from '../model/set-permission';

@Injectable({
  providedIn: 'root',
})
export class NavigationService {
  links: NavItem[] = ROUTES;
  currentItem: NavItem | null = null;
  tabs: NavTab[] = [];
  user!: UserAccess;
  private path: string = '';
  constructor() {}

  /**
   * Configure la navigation en fonction de l'utilisateur connecté.
   * @param user L'utilisateur connecté.
   * @returns Les liens de navigation configurés.
   */
  setup(user: UserAccess) {
    /**
     * Initialisation des permissions
     */
    new SetPermission(user, this.links);

    this.path = window.location.pathname;

    this.fetch();
    return this.links;
  }

  /**
   * Observe un changement d'URL et met à jour la navigation en conséquence.
   * @param url L'URL à observer.
   */
  observe(url: string) {
    this.path = url;
    this.fetch();
  }

  /**
   * Met à jour les états des liens de navigation en fonction de l'URL actuelle.
   * @returns L'élément de navigation actuellement sélectionné.
   */
  fetch(): null | NavItem {
    this.tabs = [];
    /**
     * Les liens au premier niveau
     */
    for (const lk of this.links) {
      lk.open = false;
      lk.active = false;

      /**
       * Lorsque les liens du premier niveau n'ont pas de sous menu
       * Dans le sidebar
       */
      if (lk.href == this.path) {
        //Les onglets
        if (lk.tabs && lk.tabs.length > 0) {
          for (const tab of lk.tabs) {
            tab.active = false;
            // Assignation des onglets
            if (tab.href == this.path || this.path.includes(tab.href)) {
              lk.active = true;
              tab.active = true;
              lk.active = true;

              this.currentItem = lk;
              this.tabs = lk.tabs;
            }
          }
        }
        lk.active = true;
        lk.open = false;
        this.currentItem = lk;
      } else {
        /**
         * Lorsque le lien du premier niveau dispose de sous-menu
         */
        if (lk.subs.length) {
          /**
           * Pour chaque sous menu pris individuellement
           */
          for (const sub of lk.subs) {
            sub.active = false;

            if (sub.href == this.path) {
              /**
               * Les onglets
               */
              for (const tab of sub.tabs) {
                tab.active = false;

                if (tab.href == this.path || this.path.includes(tab.href)) {
                  tab.active = true;
                } else {
                  tab.active = false;
                }
              }
              //Assignation
              lk.active = true;
              this.currentItem = lk;
              sub.active = true;
              this.tabs = sub.tabs;
              this.currentItem.open = true;
            } else {
              for (const tab of sub.tabs) {
                tab.active = false;
                //Si le chemin courant  correspond  à un onglet
                if (tab.href == this.path || this.path.includes(tab.href)) {
                  sub.active = true;
                  tab.active = true;
                  lk.active = true;
                  lk.open = true;
                  this.currentItem = lk;
                  this.tabs = sub.tabs;
                }
              }
            }
          }
        }

        //Les onglets
        if (lk.tabs && lk.tabs.length > 0) {
          for (const tab of lk.tabs) {
            tab.active = false;
            if (tab.href == this.path || this.path.includes(tab.href)) {
              lk.active = true;
              tab.active = true;
              lk.active = true;
              this.currentItem = lk;
              this.tabs = lk.tabs;
            }
          }
        }
      }
    }

    return this.currentItem;
  }

  /**
   * Basculer l'état d'ouverture d'un lien de navigation.
   * @param i L'index du lien à basculer.
   */
  toggle(i: number) {
    const lk = this.links[i];
    lk.open = !lk.open;
    this.links[i] = lk;
  }

  /**
   * Récupère les onglets actifs.
   * @returns Les onglets actifs.
   */
  getTabs() {
    return this.tabs;
  }
}
