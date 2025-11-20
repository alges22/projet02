import { Component, OnDestroy } from '@angular/core';
import { Subscription } from 'rxjs';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
@Component({
  selector: 'app-validation',
  templateUrl: './validation.component.html',
  styleUrls: ['./validation.component.scss'],
})
export class ValidationComponent implements OnDestroy {
  private updateSubscription!: Subscription;
  counts: Record<string, number> = {};
  candidat: any;
  categories: CategoryPermis[] = [];
  constructor(
    private breadcrumb: BreadcrumbService,
    private errorHandler: HttpErrorHandlerService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getCounter();
    this.updateSubscription = this.counter.onRefreshCount().subscribe(() => {
      this.getCounter();
    });
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

  getCounter() {
    const caller = () => {
      this.counter
        .getCounter([
          'init_justif_count',
          'rejet_justif_count',
          'validate_justif_count',
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

  ngOnDestroy() {
    this.updateSubscription.unsubscribe();
  }
}
