import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-agenda',
  templateUrl: './agenda.component.html',
  styleUrls: ['./agenda.component.scss'],
})
export class AgendaComponent {
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Agenda`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Agenda`,
        active: true,
      },
    ]);
  }

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
