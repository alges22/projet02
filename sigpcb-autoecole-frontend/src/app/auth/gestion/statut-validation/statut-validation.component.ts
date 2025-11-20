import { Component, Input } from '@angular/core';
import { Agenda } from 'src/app/core/interfaces/date';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
type Page = 'rejetes' | 'valides' | 'en-attente';
@Component({
  selector: 'app-statut-validation',
  templateUrl: './statut-validation.component.html',
  styleUrls: ['./statut-validation.component.scss'],
})
export class StatutValidationComponent {
  page: Page = 'rejetes';
  sessions: Agenda[] = [];
  counts: Record<string, number> = {};
  @Input() paginate = true;
  @Input() per_page = 25;
  constructor(
    private breadcrumb: BreadcrumbService,
    private errorHandler: HttpErrorHandlerService,
    private counter: CounterService,
    private examenService: ExamenService
  ) {}
  ngOnInit(): void {
    this._setBreadcrumbs();
    this._getExamens();
    this.getCounter();
  }

  selectPage(page: Page): void {
    this.page = page;
    this.getCounter();
  }

  selectExamen(tg: any) {
    const examen =
      this.sessions.find((session) => session.id == tg.value) || null;
    this.examenService.selectExam(examen);
  }
  getCounter() {
    this.counter
      .authCount([
        'monitoring_rejets_count',
        'monitoring_validate_count',
        'mt_pending_c',
      ])
      .pipe()
      .subscribe((response) => {
        this.counts = response.data;
      });
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Statut des validations', [
      {
        label: 'Tableau de bord',
        route: '/gestions/home',
      },
      {
        label: 'Statut des validations',
        active: true,
      },
    ]);
  }

  private _getExamens() {
    this.examenService.getExemens().subscribe((response) => {
      this.sessions = response.data;
    });
  }
}
