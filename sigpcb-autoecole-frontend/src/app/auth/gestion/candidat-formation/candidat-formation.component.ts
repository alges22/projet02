import { StorageService } from 'src/app/core/services/storage.service';
import { Component, Input } from '@angular/core';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
type Page = 'achevees' | 'en-attente';
@Component({
  selector: 'app-candidat-formation',
  templateUrl: './candidat-formation.component.html',
  styleUrls: ['./candidat-formation.component.scss'],
})
export class CandidatFormationComponent {
  page: Page = 'en-attente';

  candidat: any; // recuperer le candidat
  counts: Record<string, number> = {};

  pending_count = 0;
  @Input() paginate = true;
  @Input() per_page = 25;
  constructor(
    private breadcrumb: BreadcrumbService,
    private counter: CounterService,
    private storage: StorageService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getCounter();
  }

  selectPage(page: Page): void {
    this.page = page;
    this.storage.store('page', page);
    this.getCounter();
  }

  getCounter() {
    this.counter
      .authCount(['ds_init_c', 'mt_init_c'])
      .pipe()
      .subscribe((response) => {
        this.counts = response.data;
      });
  }
  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Formation des candidats', [
      {
        label: 'Tableau de bord',
        route: '/gestions/home',
      },
      {
        label: 'Formation  candidats',
        active: true,
      },
    ]);

    this.page = this.storage.get('page') || 'en-attente';
    if (!['achevees', 'en-attente'].includes(this.page)) {
      this.page = 'en-attente';
    }
  }
}
