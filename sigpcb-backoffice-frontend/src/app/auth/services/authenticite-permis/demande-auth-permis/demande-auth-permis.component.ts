import { Component } from '@angular/core';
import { AuthenticitePermis } from 'src/app/core/interfaces/services';
import { AuthenticiteDuPermisService } from 'src/app/core/services/authenticite-du-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-demande-auth-permis',
  templateUrl: './demande-auth-permis.component.html',
  styleUrls: ['./demande-auth-permis.component.scss'],
})
export class DemandeAuthPermisComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  authenticite_permis: AuthenticitePermis[] = [];
  onLoadAuthPermis = true;
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
  rejectData = {
    title: '',
    consigne: '',
    demandeId: 0,
  };
  validateForm = { consigne: null, authenticite_id: 0 };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private authPermisService: AuthenticiteDuPermisService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadAuthPermis = true;
    this.authenticite_permis = [];
    const states = ['init', 'pending'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.authPermisService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAuthPermis = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.authenticite_permis = this.paginate_data.data;
        this.onLoadAuthPermis = false;
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

  validate() {
    this.errorHandler.startLoader('Validation en cours ...');

    this.authPermisService
      .validate(this.validateForm)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(
          `Vous avez validé la demande  d'authenticité avec succès.`,
          'success',
          'middle'
        );
        this.errorHandler.stopLoader();
        $('#item-validation').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }
  onValidate(
    event: { data: AuthenticitePermis; state: string },
    index: number
  ): void {
    if (event.state === 'validate') {
      this.validateForm.authenticite_id = event.data.id;
      $('#item-validation').modal('show');
    } else if (event.state === 'rejected') {
      this.rejectData.title = `Rejet de demande d'authenticité de permis de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
      this.rejectData.demandeId = event.data.id;
      $('#rejet-modal').modal('show');
    }
  }

  reject(): void {
    this.errorHandler.startLoader('Rejet en cours ...');
    this.authPermisService
      .reject({
        authenticite_id: this.rejectData.demandeId,
        consigne: this.rejectData.consigne,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(`Demande rejetée  avec succès.`, 'success', 'middle');
        this.errorHandler.stopLoader();
        this.dossierIndex = null;
        $('#rejet-modal').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }
}
