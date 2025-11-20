import { Component } from '@angular/core';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { NavigationService } from 'src/app/core/services/navigation.service';
import { numberPad } from 'src/app/helpers/helpers';
@Component({
  selector: 'app-administrateurs',
  templateUrl: './administrateurs.component.html',
  styleUrls: ['./administrateurs.component.scss'],
})
export class AdministrateursComponent {
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
    this.breadcrumb.setBreadcrumbs(`Gestion d'accès`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Gestion d'accès`,
        active: true,
      },
    ]);
  }

  numberPad(value: number) {
    return numberPad(value);
  }

  private getCounter() {
    this.counter
      .getCounter([
        'users_count',
        'roles_count',
        'titres_count',
        'uniteadmins_count',
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
