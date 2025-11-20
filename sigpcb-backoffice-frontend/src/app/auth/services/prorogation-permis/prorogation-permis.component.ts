import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-prorogation-permis',
  templateUrl: './prorogation-permis.component.html',
  styleUrls: ['./prorogation-permis.component.scss'],
})
export class ProrogationPermisComponent {
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private navigation: NavigationService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getCounter();
    this._setTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Les titres dérivés`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Prorogation de permis`,
        active: true,
      },
    ]);
  }

  getCounter() {
    this.counter
      .getCounter([
        'prorogation_init_count',
        'prorogation_rejets_count',
        'prorogation_valide_count',
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
