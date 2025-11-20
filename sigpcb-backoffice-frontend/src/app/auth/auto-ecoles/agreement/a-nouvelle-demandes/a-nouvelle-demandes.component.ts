import { Component } from '@angular/core';
import { Agreement } from 'src/app/core/interfaces/auto-ecoles';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-a-nouvelle-demandes',
  templateUrl: './a-nouvelle-demandes.component.html',
  styleUrls: ['./a-nouvelle-demandes.component.scss'],
})
export class ANouvelleDemandesComponent {
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
    const states = ['init', 'pending'];
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
      const data = {
        d_agrement_id: event.agrementId,
        autoecole_name: event?.agrement.auto_ecole,
        autoecole_email: event?.agrement.email_pro,
        autoecole_phone: event?.agrement.telephone_pro,

        autoecole_adresse: event?.agrement?.commune.name ?? '',
        num_ifu: event?.agrement.ifu,
        commune_id: event?.agrement.commune_id,
        departement_id: event?.agrement.departement_id,
      };
      this.aecoleService
        .validate(data)
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
      this.rejectData.title = `Rejet de demande d'agrément pour l'auto école <span class="text-uppercase"> ${event.agrement?.auto_ecole}</span>`;
      this.rejectData.agrementId = event.agrementId;
      $('#rejet-modal').modal('show');
    }
  }

  reject(): void {
    this.errorHandler.startLoader('Rejet en cours ...');
    this.aecoleService
      .reject(this.rejectData.agrementId, {
        motif: this.rejectData.motif,
        consignes: this.rejectData.consigne,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(
          `Demande d'agrément rejetée  avec succès.`,
          'success',
          'middle'
        );
        this.errorHandler.stopLoader();
        $('#rejet-modal').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }
}
