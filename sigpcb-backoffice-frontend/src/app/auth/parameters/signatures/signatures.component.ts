import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-signatures',
  templateUrl: './signatures.component.html',
  styleUrls: ['./signatures.component.scss'],
})
export class SignaturesComponent {
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

  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Signatures`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Signatures`,
        active: true,
      },
    ]);
  }

  getCounter() {
    this.counter
      .getCounter(['signataires_count', 'acte_signes_count'])
      .pipe()
      .subscribe((response) => {
        this.counts = response.data;
      });
  }
}
