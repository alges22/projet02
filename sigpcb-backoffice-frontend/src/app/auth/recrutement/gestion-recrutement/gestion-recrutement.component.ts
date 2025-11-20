import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-gestion-recrutement',
  templateUrl: './gestion-recrutement.component.html',
  styleUrls: ['./gestion-recrutement.component.scss'],
})
export class GestionRecrutementComponent {
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
    this.breadcrumb.setBreadcrumbs(`Enrôlements`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },

      {
        label: `Examinateurs`,
        active: true,
      },
    ]);
  }

  getCounter() {
    const caller = () => {
      this.counter
        .getCounter([
          'r_gestion_pending_count',
          'r_gestion_rejet_count',
          'r_gestion_validate_count',
        ])
        .pipe()
        .subscribe((response) => {
          this.counts = response.data;
        });
    };
    //Call fisrtly
    caller();
    this.counter.onRefreshCount().subscribe((response) => {
      caller();
    });
  }

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
