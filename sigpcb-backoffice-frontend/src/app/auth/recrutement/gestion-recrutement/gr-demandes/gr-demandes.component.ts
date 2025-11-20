import { Component, Input } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import {
  RecrutementCandidat,
  RecrutementDemande,
  RecrutementEntreprise,
} from 'src/app/core/interfaces/recreutement';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-gr-demandes',
  templateUrl: './gr-demandes.component.html',
  styleUrls: ['./gr-demandes.component.scss'],
})
export class GrDemandesComponent {
  previewUrl: string | null = null;
  pageNumber = 1;
  candidats: RecrutementCandidat[] = [];
  sessions: RecrutementDemande[] = [];
  candidatIndex: number | null = null;
  demandeIndex: number | null = null;
  onLoadSession = true;
  sessionPaginateData: any = null;
  candidatPaginateData: any = null;
  selectedSession: RecrutementDemande | null = null;
  sessionToRejet: RecrutementDemande | null = null;
  filters = {
    search: null as string | null | number,
  };
  rejectData: any = {};
  rejectMessage: string | null = null;
  constructor(
    private recrutementExaminateurService: RecrutemmentExaminateurService,
    private errorHandler: HttpErrorHandlerService,
    private counter: CounterService
  ) {}

  onRejected() {
    this.rejectMessage = null;
    if (
      this.rejectData.motif == 'undefined' ||
      this.rejectData.motif == undefined
    ) {
      this.rejectMessage = 'Veuillez sélectionner un motif';
      return;
    }
    if (this.sessionToRejet) {
      this.errorHandler.startLoader('Rejet en cours ...');
      this.rejectData.recrutement_id = this.sessionToRejet.id;
      this.recrutementExaminateurService
        .validateOrRejectDemandeRecrutement(this.rejectData, 'rejet')
        .pipe(this.errorHandler.handleServerErrors((response) => {}))
        .subscribe((response) => {
          this.get();
          this.selectedSession = null;
          this.sessionToRejet = null;
          this.rejectData = {};
          this.demandeIndex = null;
          $(`#reject-modal`).modal('hide');
          this.freshCounts();
          this.errorHandler.stopLoader();
          emitAlertEvent('Le rejet a été effectué avec succès', 'success');
          this.selectedSession = null;
        });
    }
  }
  ngOnInit(): void {
    this.get();
  }

  openRejectModal(item: RecrutementDemande) {
    this.sessionToRejet = item;
    $(`#reject-modal`).modal('show');
  }

  assets(path?: string, open = false) {
    this.previewUrl = environment.examinateur.asset + path;
    if (open) {
      $('#img-previews').modal('show');
    }
    return this.previewUrl;
  }
  showSession(selectedSession: RecrutementDemande, i: number) {
    this.selectedSession = selectedSession;
    if (this.demandeIndex === i) {
      this.demandeIndex = null;
    } else {
      this._getCandidats();
      this.demandeIndex = i;
    }

    $('#show-session').modal('show');
  }

  openCandidat(i: number) {
    if (this.candidatIndex === i) {
      this.candidatIndex = null;
    } else {
      this.candidatIndex = i;
    }
  }

  get() {
    this.sessions = [];
    this.onLoadSession = true;
    const states = ['pending'];
    this.recrutementExaminateurService
      .getEntrepriseSessions(
        states,
        null,
        this.pageNumber,
        this.filters.search != 'null' ? this.filters.search : null
      )
      .pipe(this.errorHandler.handleServerErrors((response) => {}))
      .subscribe((response) => {
        this.onLoadSession = false;
        this.sessionPaginateData = response.data;
        const data = this.sessionPaginateData.data;
        if (data) {
          this.sessions = data;
        }
      });
  }
  onValidate(item: RecrutementDemande) {
    this.errorHandler.startLoader('Validation en cours ...');
    this.recrutementExaminateurService
      .validateOrRejectDemandeRecrutement(
        {
          recrutement_id: item.id,
        },
        'validate'
      )
      .pipe(this.errorHandler.handleServerErrors((response) => {}))
      .subscribe((response) => {
        this.get();
        this.errorHandler.stopLoader();
        this.selectedSession = null;
        this.demandeIndex = null;
        this.freshCounts();
      });
  }
  private _getCandidats() {
    if (this.selectedSession) {
      this.onLoadSession = true;
      //const states = ['pending'];
      this.errorHandler.startLoader('Chargement des candidats ...');
      this.recrutementExaminateurService
        .getEntrepriseSessionCandidats(this.selectedSession.id, null, null)
        .pipe(this.errorHandler.handleServerErrors((response) => {}))
        .subscribe((response) => {
          this.onLoadSession = false;
          this.candidatPaginateData = response.data;

          const data = this.candidatPaginateData.data;
          if (data) {
            this.candidats = data;
          }
          this.errorHandler.stopLoader();
        });
    }
  }

  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.sessionPaginateData?.total ?? 0,
    };
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }

  private freshCounts() {
    this.counter.refreshCount();
  }
}
