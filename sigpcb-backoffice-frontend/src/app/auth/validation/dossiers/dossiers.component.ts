import { Component, OnDestroy } from '@angular/core';
import { Subscription } from 'rxjs';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { NavTab } from 'src/app/core/interfaces/navbar-link';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { NavigationService } from 'src/app/core/services/navigation.service';
type Page = 'nouvelle-demandes' | 'valides' | 'en-attente';
@Component({
  selector: 'app-dossiers',
  templateUrl: './dossiers.component.html',
  styleUrls: ['./dossiers.component.scss'],
})
export class DossiersComponent implements OnDestroy {
  page: Page = 'nouvelle-demandes';
  counts: Record<string, number> = {};
  candidat: any;
  categories: CategoryPermis[] = [];
  tabs: NavTab[] = [];
  private updateSubscription!: Subscription;
  constructor(
    private breadcrumb: BreadcrumbService,
    private categoryPermisService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService,
    private counter: CounterService,
    private navigation: NavigationService
  ) {}

  ngOnInit(): void {
    this.updateSubscription = this.counter.onRefreshCount().subscribe(() => {
      this.getCounter();
    });
    this._setTabs();
    this._setBreadcrumbs();
    this.getCategories();
    this.getCounter();
  }

  selectPage(page: Page): void {
    this.page = page;
  }
  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Validation', [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: 'Validation',
        active: true,
      },
    ]);
  }
  getCategories() {
    this.errorHandler.startLoader();
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        this.errorHandler.stopLoader();
      });
  }
  getCounter() {
    this.counter
      .getCounter([
        'pending_monitoring_count',
        'rejet_monitoring_count',
        'validate_monitoring_count',
      ])
      .pipe()
      .subscribe((response) => {
        this.counts = response.data;
      });
  }

  ngOnDestroy(): void {
    this.updateSubscription.unsubscribe();
  }
  private _setTabs() {
    this.tabs = this.navigation.getTabs();
  }
}
