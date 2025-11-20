import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-signature-topbar',
  templateUrl: './signature-topbar.component.html',
  styleUrls: ['./signature-topbar.component.scss'],
})
export class SignatureTopbarComponent {
  @Input('default') defaultMenuId = 0;
  menuSelected: number | null = null;
  menus = [
    {
      label: 'Signataires',
      icon: 'person-lines-fill',
      href: '/parametres/signatures',
      active: true,
      menuId: 0,
    },

    {
      label: 'Acte Signés',
      icon: 'file-earmark-check',
      href: '/parametres/signatures/acte-signes',
      active: false,
      menuId: 1,
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
