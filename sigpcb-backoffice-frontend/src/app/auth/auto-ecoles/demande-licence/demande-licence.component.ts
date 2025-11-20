import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CompositionService } from 'src/app/core/services/composition.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { NavigationService } from 'src/app/core/services/navigation.service';

@Component({
  selector: 'app-demande-licence',
  templateUrl: './demande-licence.component.html',
  styleUrls: ['./demande-licence.component.scss'],
})
export class DemandeLicenceComponent {
  counts: Record<string, number> = {};
  tabs: NavTab[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private composition: CompositionService,
    private errorHandler: HttpErrorHandlerService,
    private router: Router,
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
        label: `Demande de licences`,
        active: true,
      },
    ]);
  }

  getCounter() {
    this.counter
      .getCounter([
        'demande_licence_news_count',
        'demande_licence_rejets_count',
        'demande_licence_valides_count',
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
