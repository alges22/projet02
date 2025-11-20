import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-param-base-topbar',
  templateUrl: './param-base-topbar.component.html',
  styleUrls: ['./param-base-topbar.component.scss'],
})
export class ParamBaseTopbarComponent {
  @Input('default') defaultMenuId = 0;
  menuSelected: number | null = null;
  menus = [
    {
      label: 'Langues',
      icon: 'translate',
      href: '/parametres/base',
      active: true,
      menuId: 0,
    },

    {
      label: 'Agrégateurs',
      icon: 'credit-card-2-front',
      href: '/parametres/base/agregateurs',
      active: false,
      menuId: 2,
    },

    {
      label: 'Catégorie de permis',
      icon: 'car-front',
      href: '/parametres/base/categorie-permis',
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
