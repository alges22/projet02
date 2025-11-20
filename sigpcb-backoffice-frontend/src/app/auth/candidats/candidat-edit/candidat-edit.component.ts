import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Agenda } from 'src/app/core/interfaces/examens';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { MonitoringService } from 'src/app/core/services/monitoring.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-candidat-edit',
  templateUrl: './candidat-edit.component.html',
  styleUrls: ['./candidat-edit.component.scss'],
})
export class CandidatEditComponent {
  examens: Agenda[] = [];
  accepted = false;
  submitted = false;
  form = {
    npi: '',
    examen_id: 0,
  };
  candidat: {
    npi: string;
    annexe: AnnexeAnatt;
    candidat: {};
    categorie_permis: CategoryPermis;
    auto_ecole: {
      name: string;
    };
  } | null = null;
  info: any;
  filters = {
    sessionSelected: 0,
    search: null as number | null,
  };
  auth: any;
  constructor(
    private readonly monitoringService: MonitoringService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly examenService: ExamenService,
    private readonly autService: AuthService,
    private readonly candidatService: CandidatService,
    private readonly router: Router
  ) {}

  ngOnInit(): void {
    this.getExamens();
    this.auth = this.autService.auth();
  }

  private getExamens() {
    this.examenService
      .getExemens({
        not_used: 1,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
      });
  }
  getCandidat() {
    const filters: any = [
      { state: 'pending' },
      { examen_id: this.filters.sessionSelected, npi: this.form.npi },
    ];

    if (this.form.npi.length < 10) {
      emitAlertEvent('Veuillez taper un npi valide');
      return;
    }
    this.errorHandler.startLoader();
    this.monitoringService
      .all(filters)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        let data = response.data?.paginate_data?.data;
        if (Array.isArray(data)) {
          data = this.sortBy(data, 'id', false);

          this.candidat = data[0] ?? null;
          if (this.candidat) {
            this.info = this.candidat.candidat;
          }
        }

        this.errorHandler.stopLoader();
      });
  }

  sortBy<T>(array: T[], key: keyof T, ascending: boolean = true): T[] {
    return array.sort((a, b) => {
      const valueA = a[key];
      const valueB = b[key];

      if (valueA === valueB) {
        return 0;
      }

      const comparison = valueA > valueB ? 1 : -1;
      return ascending ? comparison : -comparison;
    });
  }

  validate() {
    if (this.candidat) {
      this.form.npi = this.candidat.npi;
      this.form.examen_id = this.filters.sessionSelected;
    }

    if (this.form.npi.length < 10 || (!this.form.examen_id && !this.accepted)) {
      emitAlertEvent('Veuillez remplir tous les champs nécessaires');
      return;
    }

    this.candidatService
      .authorizeExamen(this.form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.emitSuccessAlert(response.message);

        this.form.examen_id = 0;
        this.form.npi = '';

        this.router.navigate(['/dashboard']);
      });
  }

  generateMessage(): string {
    if (!this.auth || !this.filters.sessionSelected || !this.candidat) {
      return '';
    }

    const sessionName = this.examens.find(
      (examen) => examen.id == this.filters.sessionSelected
    )?.session_long;

    return `Je soussigné(e) ${this.auth.first_name} ${this.auth.last_name} autorise ${this.info.nom} ${this.info.prenoms} à composer la session "${sessionName}" sans payer.`;
  }

  get filtredExamens() {
    return this.examens.filter((e) => {
      const annexe_ids = e.annexe_ids ?? [];
      if (this.candidat?.annexe) {
        return annexe_ids.includes(this.candidat.annexe.id);
      }
      return false;
    });
  }
}
