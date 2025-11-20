import { Component } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { DossierCandidat } from 'src/app/core/interfaces/candidat';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { MonitoringService } from 'src/app/core/services/monitoring.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-dossier-valides',
  templateUrl: './dossier-valides.component.html',
  styleUrls: ['./dossier-valides.component.scss'],
})
export class DossierValidesComponent {
  dossierIndex: number | null = null;
  dossiersCandidats: DossierCandidat[] = [];
  monitoring: any[] = [];
  onLoadMonitoring = true;

  paginate_data!: any;
  pageNumber = 1;
  categories: CategoryPermis[] = []; //
  /**
   * Les paramètres de filtrage
   */
  filters = {
    permisSelected: null as number | null,
    annexeSelected: null as number | null,
    sessionSelected: null as number | null,
    search: null as number | null,
  };

  annexeAnatts: AnnexeAnatt[] = [];
  examens: any[] = [];
  /**
   * Les données du rejet
   */
  rejectData = {
    title: '',
    consigne: '',
    motif: 'Dossier erroné',
    suiviId: 0,
  };

  constructor(
    private monitoringService: MonitoringService,
    private categoryPermisService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService,
    private annexeAnattService: AnnexeAnattService,
    private examenService: ExamenService,
    private counter: CounterService
  ) {}
  ngOnInit(): void {
    this.getMonitoring();
    this.getCategories();
    this.getExamens();
    this.getAnnexeAnatt();
  }
  showDossier(i: number): void {
    if (this.dossierIndex === i) {
      this.dossierIndex = null;
    } else {
      this.dossierIndex = i;
    }
  }

  onValidate(event: any, index: number): void {
    if (event.state === 'rejected') {
      this.rejectData.title = `Rejet de <span class="text-uppercase"> ${event.candidat?.nom} ${event.candidat?.prenoms}</span>`;
      this.rejectData.suiviId = event.suiviId;

      $('#rejet-modal').modal('show');
    }
  }

  reject(): void {
    this.errorHandler.startLoader('Rejet en cours ...');
    this.monitoringService
      .reject(this.rejectData.suiviId, {
        motif: this.rejectData.motif,
        consignes: this.rejectData.consigne,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
        if (this.dossierIndex) {
          this.dossiersCandidats.splice(this.dossierIndex, 1);
        }
        this.dossierIndex = null;
        this.counter.refreshCount();
        this.getMonitoring();
        $('#rejet-modal').modal('hide');
      });
  }

  /**
   * Obtenir les annexes Anatt.
   */

  private getAnnexeAnatt() {
    this.annexeAnattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexeAnatts = response.data;
      });
  }

  private getExamens() {
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
      });
  }
  /**
   * Obtenez les données de surveillance.
   *
   * Filtres @param Les filtres à appliquer à la requête.
   */
  getMonitoring() {
    const filters: any = [
      { state: 'validate' },
      { page: this.pageNumber },
      { annexe_id: this.filters.annexeSelected },
      { categorie_permis_id: this.filters.permisSelected },
      { examen_id: this.filters.sessionSelected, search: this.filters.search },
    ];
    this.onLoadMonitoring = true;
    this.monitoringService
      .all(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadMonitoring = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.paginate_data = data.paginate_data;
        this.monitoring = this.paginate_data.data;
        this.onLoadMonitoring = false;
      });
  }
  /**
   * Obtenir les catégories de permis.
   */
  getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
      });
  }

  getPermis(category_id: number, def = '') {
    let permis = this.categories.filter((c) => c.id === category_id)[0];
    if (permis) {
      return permis;
    }
    return {
      name: '',
    };
  }

  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data?.total ?? 0,
    };
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.getMonitoring();
  }
}
