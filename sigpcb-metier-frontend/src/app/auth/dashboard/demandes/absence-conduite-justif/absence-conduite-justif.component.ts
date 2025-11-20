import { Component } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-absence-conduite-justif',
  templateUrl: './absence-conduite-justif.component.html',
  styleUrls: ['./absence-conduite-justif.component.scss'],
})
export class AbsenceConduiteJustifComponent {
  justification = '';

  autoEcole = '';
  quickvInfosPermis: any;
  isValidInfosPermis = false;

  currentPage = 'infos-sur-le-permis';
  candidat_type: any;
  fichier_justif: any;
  userHome: any;
  todo: any;
  dossier_id: any;
  isloadingSave = false;
  nom_permis: any;
  file: File | null = null;
  constructor(
    private storage: StorageService,
    private candidatService: CandidatService,
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this._getUserConnected();
    this.todo = this.storage.get('todo');
    this._getLastDossierCandidatWithID();
  }

  private _getUserConnected() {
    this.userHome = this.authService.storageService().get('auth');
  }

  private _getLastDossierCandidatWithID() {
    if (this.userHome) {
      this.errorHandler.startLoader();
      this.candidatService
        .getLastDossierCandidatWithId()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          if (response.data && response.data.dossier) {
            this.dossier_id = response.data.dossier.id;
            this.nom_permis = response.data.nom_permis;
          }

          this.errorHandler.stopLoader();
        });
    }
  }

  ngAfterViewInit(): void {
    //@ts-ignore
    this.quickvInfosPermis = new QvForm('#infos-sur-le-permis');
    this.quickvInfosPermis.init();

    this.quickvInfosPermis.onValidate((qvForm: any) => {
      this.isValidInfosPermis = qvForm.passes();
    });
  }

  /**
   * Vérifier si la page d'informations sur le permis est valide
   * @returns
   */
  justifFormIsValid() {
    return (
      this.isValidInfosPermis && this.file !== undefined && this.file !== null
    );
  }

  goto(page: string, event: Event) {
    if (page == 'completed') {
      this.save(event);
    }
  }
  save(event: Event) {
    event.preventDefault();
    this.candidat_type = this.storage.get('userType');
    this.fichier_justif = this.file;
    const formData = new FormData();
    formData.append('nom_permis', this.nom_permis);
    formData.append('motif', this.justification);
    if (this.fichier_justif) {
      formData.append('fichier_justif', this.fichier_justif);
    }
    formData.append('dossier_candidat_id', this.dossier_id);
    formData.append('examen_type', 'conduite');
    this.isloadingSave = true;
    this.post(formData);
  }

  private post(data: any) {
    this.candidatService
      .postJustificationAbsenceCandidat(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloadingSave = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.currentPage = 'completed';
          this.isloadingSave = false;
        }
      });
  }
  // Changer le fichier associé à une pièce
  onFileChange(file: File | undefined) {
    this.file = file || null;
  }
}
