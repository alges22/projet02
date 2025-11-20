import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-territoriales',
  templateUrl: './territoriales.component.html',
  styleUrls: ['./territoriales.component.scss'],
})
export class TerritorialesComponent {
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private counter: CounterService,
    private navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getCounter();
    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Espace unité territoriale`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Espace unité territoriale`,
        active: true,
      },
    ]);
  }

  getCounter() {
    this.counter
      .getCounter([
        'annexes_anatt_count',
        'departements_count',
        'communes_count',
        'arrondissements_count',
      ])
      .pipe()
      .subscribe((response) => {
        this.counts = response.data;
      });
  }

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
