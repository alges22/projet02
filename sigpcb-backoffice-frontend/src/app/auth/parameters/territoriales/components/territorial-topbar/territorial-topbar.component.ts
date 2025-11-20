import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-territorial-topbar',
  templateUrl: './territorial-topbar.component.html',
  styleUrls: ['./territorial-topbar.component.scss'],
})
export class TerritorialTopbarComponent {
  @Input('default') defaultMenuId = 0;
  menuSelected: number | null = null;
  menus = [
    {
      label: 'Annexes ANaTT',
      icon: 'buildings',
      href: '/parametres/territoriales',
      active: true,
      menuId: 0,
    },

    {
      label: 'Départements',
      icon: 'menu-button',
      href: '/parametres/territoriales/departements',
      active: false,
      menuId: 1,
    },

    {
      label: 'Communes',
      icon: 'menu-up',
      href: '/parametres/territoriales/communes',
      active: false,
      menuId: 2,
    },

    {
      label: 'Arrondissements',
      icon: 'map',
      href: '/parametres/territoriales/arrondissements',
      active: false,
      menuId: 3,
    },
  ];

  ngOnInit(): void {
    this.menuSelected = this.defaultMenuId;
  }

  onSelectMenu(event: Event, menuId: number) {
    event.preventDefault();
    this.menus.forEach((m) => (m.active = false));
    this.menuSelected = menuId;
  }
}
