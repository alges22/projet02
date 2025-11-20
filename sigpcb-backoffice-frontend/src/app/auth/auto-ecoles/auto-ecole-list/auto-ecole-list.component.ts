import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-auto-ecole-list',
  templateUrl: './auto-ecole-list.component.html',
  styleUrls: ['./auto-ecole-list.component.scss'],
})
export class AutoEcoleListComponent {
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
    this.breadcrumb.setBreadcrumbs(`Auto-Ecoles`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Auto-Ecoles`,
        active: true,
      },
    ]);
  }

  getCounter() {
    this.counter
      .getCounter([
        'auto_ecoles_count',
        'licences_actives_count',
        'licences_expirees_count',
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
