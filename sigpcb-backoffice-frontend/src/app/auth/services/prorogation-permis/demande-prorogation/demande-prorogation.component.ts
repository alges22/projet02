import { Component } from '@angular/core';
import { ProrogationPermis } from 'src/app/core/interfaces/services';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { EchangePermisService } from 'src/app/core/services/echange-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ProrogationPermisService } from 'src/app/core/services/prorogation-permis.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-demande-prorogation',
  templateUrl: './demande-prorogation.component.html',
  styleUrls: ['./demande-prorogation.component.scss'],
})
export class DemandeProrogationComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  prorogation_permis: ProrogationPermis[] = [];
  onLoadProrogationPermis = true;
  dossierIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
  };
  /**
   * Les données du rejet
   */
  decisionData = {
    title: '',
    consigne: '',
    demandeId: 0,
    state: '',
  };
  categories: any;
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private prorogationPermisService: ProrogationPermisService,
    private counter: CounterService // private categoryPermisService: CategoryPermisService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadProrogationPermis = true;
    this.prorogation_permis = [];
    const states = ['init', 'pending'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.prorogationPermisService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadProrogationPermis = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.prorogation_permis = this.paginate_data.data;
        this.onLoadProrogationPermis = false;
      });
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }

  showDossier(i: number): void {
    if (this.dossierIndex === i) {
      this.dossierIndex = null;
    } else {
      this.dossierIndex = i;
    }
  }
  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data.total ?? 0,
    };
  }

  onValidate(
    event: { data: ProrogationPermis; state: string },
    index: number
  ): void {
    this.decisionData.state = event.state;
    if (event.state === 'validate') {
      this.decisionData.title = `Validation de demande de prorogation de permis de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
      this.decisionData.demandeId = event.data.id;
      $('#decision-modal').modal('show');
    } else if (event.state === 'rejected') {
      this.decisionData.title = `Rejet de demande de prorogation de permis de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
      this.decisionData.demandeId = event.data.id;
      $('#decision-modal').modal('show');
    }
  }

  reject(): void {
    this.errorHandler.startLoader('Rejet en cours ...');
    this.prorogationPermisService
      .reject(this.decisionData.demandeId, {
        motif: '',
        consignes: this.decisionData.consigne,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(`Demande rejetée  avec succès.`, 'success', 'middle');
        this.errorHandler.stopLoader();
        this.dossierIndex = null;
        $('#decision-modal').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }

  validate(): void {
    this.errorHandler.startLoader('Validation en cours ...');
    this.prorogationPermisService
      .validate(this.decisionData.demandeId, {
        motif: '',
        consignes: this.decisionData.consigne,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(`Demande validée  avec succès.`, 'success', 'middle');
        this.errorHandler.stopLoader();
        this.dossierIndex = null;
        $('#decision-modal').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }
}
