import { Component } from '@angular/core';
import { DuplicataRemplacement } from 'src/app/core/interfaces/services';
import { CounterService } from 'src/app/core/services/counter.service';
import { DuplicataRemplacementService } from 'src/app/core/services/duplicata-remplacement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-rejet-remplacement-permis',
  templateUrl: './rejet-remplacement-permis.component.html',
  styleUrls: ['./rejet-remplacement-permis.component.scss'],
})
export class RejetRemplacementPermisComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  duplicatas: DuplicataRemplacement[] = [];
  onLoadAuthPermis = true;
  dossierIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
    type: null as string | null,
  };
  /**
   * Les données du rejet
   */
  rejectData = {
    title: '',
    consigne: '',
    demandeId: 0,
  };
  validateForm = { consigne: null, duplicata_id: 0 };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private duplicaService: DuplicataRemplacementService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadAuthPermis = true;
    this.duplicatas = [];
    const states = ['rejected'];
    this.duplicaService
      .get(states, this.pageNumber, this.filters.search, this.filters.type)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAuthPermis = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.duplicatas = this.paginate_data.data;
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

    this.duplicaService
      .validate(this.validateForm)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(
          `Vous avez validé la demande du duplicata ou remplacement du permis avec succès.`,
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
    event: { data: DuplicataRemplacement; state: string },
    index: number
  ): void {
    if (event.state === 'validate') {
      this.validateForm.duplicata_id = event.data.id;
      $('#item-validation').modal('show');
    } else if (event.state === 'rejected') {
      this.rejectData.title = `Rejet de demande  du duplicata ou remplacement du permis de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
      this.rejectData.demandeId = event.data.id;
      $('#rejet-modal').modal('show');
    }
  }

  reject(): void {
    this.errorHandler.startLoader('Rejet en cours ...');
    this.duplicaService
      .reject({
        duplicata_id: this.rejectData.demandeId,
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
