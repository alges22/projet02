import { Component } from '@angular/core';
import {
  RecrutementCandidat,
  RecrutementDemande,
} from 'src/app/core/interfaces/recreutement';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-gr-demandes-validate',
  templateUrl: './gr-demandes-validate.component.html',
  styleUrls: ['./gr-demandes-validate.component.scss'],
})
export class GrDemandesValidateComponent {
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
  filters = {
    search: null as string | null | number,
  };

  constructor(
    private recrutementExaminateurService: RecrutemmentExaminateurService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
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
    const states = ['validate'];
    this.recrutementExaminateurService
      .getEntrepriseSessions(states, null, null, null)
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
}
