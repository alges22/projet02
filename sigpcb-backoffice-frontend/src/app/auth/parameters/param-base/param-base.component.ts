import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-param-base',
  templateUrl: './param-base.component.html',
  styleUrls: ['./param-base.component.scss'],
})
export class ParamBaseComponent {
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
    this.breadcrumb.setBreadcrumbs(`Paramétrage de base`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Paramétrage de base`,
        active: true,
      },
    ]);
  }

  getCounter() {
    this.counter
      .getCounter([
        'langues_count',
        'agregateurs_count',
        'category_permis_count',
        'restrictions_count',
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
