import { Component } from '@angular/core';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-agreement-rejetes',
  templateUrl: './agreement-rejetes.component.html',
  styleUrls: ['./agreement-rejetes.component.scss'],
})
export class AgreementRejetesComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  agrements: any[] = [];
  onLoadAgrement = true;
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
    motif: 'Dossier erroné',
    agrementId: 0,
  };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private aecoleService: AecoleService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadAgrement = true;
    this.agrements = [];
    const states = ['rejected'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.aecoleService
      .getAgrement(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAgrement = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.agrements = this.paginate_data.data;
        this.onLoadAgrement = false;
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

  onValidate(event: any, index: number): void {
    if (event.state === 'validate') {
      this.errorHandler.startLoader('Validation en cours ...');
      console.log(event);
      this.aecoleService
        .validate(event.agrementId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          emitAlertEvent(
            `Vous avez validé la demande d'agrément de l'auto école <b>${event.agrement?.auto_ecole}</b>  avec succès.`,
            'success',
            'middle'
          );
          this.errorHandler.stopLoader();
          this.agrements = this.agrements.filter(
            (ae) => ae.id !== event.agrementId
          );
          this.counter.refreshCount();
          this.get();
          this.dossierIndex = index + 1;
        });
    } else if (event.state === 'rejected') {
      console.log(event);
      this.rejectData.title = `Rejet de demande d'agrément pour l'auto école <span class="text-uppercase"> ${event.agrement?.auto_ecole}</span>`;
      this.rejectData.agrementId = event.agrementId;
      $('#rejet-modal').modal('show');
    }
  }
}
