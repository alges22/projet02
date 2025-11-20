import { Component } from '@angular/core';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-nouvelle-licence',
  templateUrl: './nouvelle-licence.component.html',
  styleUrls: ['./nouvelle-licence.component.scss'],
})
export class NouvelleLicenceComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  newlicences: any[] = [];
  onLoadNouvelleLicence = true;
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
    nouvellelicenceId: 0,
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
    this.onLoadNouvelleLicence = true;
    this.newlicences = [];
    const states = ['init', 'pending'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.aecoleService
      .getNouvelleLicence(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadNouvelleLicence = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.newlicences = this.paginate_data.data;
        this.onLoadNouvelleLicence = false;
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
        d_licence_id: event.nouvellelicenceId,
        autoecole_name: event?.nouvellelicence?.autoecole.name,
        email_promoteur: event?.nouvellelicence?.promoteur_info.email,
      };
      this.aecoleService
        .validateNouvelleLicence(data)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          emitAlertEvent(
            `Vous avez validé la demande de licence de l'auto école <b>${event.nouvellelicence?.autoecole.name}</b>  avec succès.`,
            'success',
            'middle'
          );
          this.errorHandler.stopLoader();
          this.newlicences = this.newlicences.filter(
            (ae) => ae.id !== event.nouvellelicenceId
          );
          this.counter.refreshCount();
          this.get();
          this.dossierIndex = index + 1;
        });
    } else if (event.state === 'rejected') {
      this.rejectData.title = `Rejet de demande de licence pour l'auto école <span class="text-uppercase"> ${event.nouvellelicence?.autoecole.name}</span>`;
      this.rejectData.nouvellelicenceId = event.nouvellelicenceId;
      $('#rejet-modal').modal('show');
    }
  }

  reject(): void {
    this.errorHandler.startLoader('Rejet en cours ...');
    this.aecoleService
      .rejectNouvelleLicence(this.rejectData.nouvellelicenceId, {
        motif: this.rejectData.motif,
        consigne: this.rejectData.consigne,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(
          `Demande de licence rejetée  avec succès.`,
          'success',
          'middle'
        );
        this.errorHandler.stopLoader();
        // if (this.dossierIndex) {
        //   this.dossiersCandidats.splice(this.dossierIndex, 1);
        // }
        // this.dossierIndex = null;
        $('#rejet-modal').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }
}
