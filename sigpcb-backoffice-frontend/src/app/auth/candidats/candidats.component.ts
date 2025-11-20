import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-candidats',
  templateUrl: './candidats.component.html',
  styleUrls: ['./candidats.component.scss'],
})
export class CandidatsComponent {
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private readonly breadcrumb: BreadcrumbService,
    private readonly navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Candidats`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Candidats`,
        active: true,
      },
    ]);
  }

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
