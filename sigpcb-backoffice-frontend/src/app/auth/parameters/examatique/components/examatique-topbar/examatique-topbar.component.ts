import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-examatique-topbar',
  templateUrl: './examatique-topbar.component.html',
  styleUrls: ['./examatique-topbar.component.scss'],
})
export class ExamatiqueTopbarComponent {
  @Input('default') defaultMenuId = 0;
  menuSelected: number | null = null;
  menus = [
    {
      label: 'Questions',
      icon: 'question-circle',
      href: '/parametres/examatique',
      active: true,
      menuId: 0,
    },
    {
      label: 'Réponses possibles',
      icon: 'reply',
      href: '/parametres/examatique/reponses',
      active: false,
      menuId: 1,
    },
    {
      label: 'Barème Conduite',
      icon: 'file-text',
      href: '/parametres/examatique/bareme-conduites',
      active: false,
      menuId: 2,
    },
    {
      label: 'Chapitres',
      icon: 'list-ol',
      href: '/parametres/examatique/chapitres',
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
