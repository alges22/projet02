import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { User } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import {
  emitAlertEvent,
  isFile,
  is_array,
  redirectTo,
} from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-edit-dossier',
  templateUrl: './edit-dossier.component.html',
  styleUrls: ['./edit-dossier.component.scss'],
})
export class EditDossierComponent {
  isloading = false;
  isloadingSave = false;
  auth: User | null = null;

  langues: any[] = [];
  langueSelected: any;
  langue = '';
  imageSrc: string = '';
  inputId = 'openImageModal';
  fiche_medical: any;
  groupage_test: any;
  todo: any;

  groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];

  restrictions: any[] = [
    {
      id: 0,
      name: 'Aucune',
      description: null,
      created_at: null,
      updated_at: null,
    },
  ];

  group = '';
  restriction: any;

  pieces = [
    {
      label: 'Fiche médicale (image)',
      file: undefined as undefined | File,
      name: 'medical',
      content: 'Fiche médicale',
      src: '',
    },
    {
      label: 'Fiche de test de groupage (image)',
      file: undefined as undefined | File,
      name: 'groupage',
      content: 'Test de groupage',
      src: '',
    },
  ];

  clearInput: number = 0;
  dossierSession: any = null;
  userHome: any;
  serverResponseReceived: boolean = false;
  canEdit = true;
  constructor(
    private router: Router,
    private errorHandler: HttpErrorHandlerService,
    private authService: AuthService,
    private candidatService: CandidatService,
    private storage: StorageService
  ) {}

  // Sélectionner un groupe sanguin
  selectGroup(group: string) {
    this.group = group;
  }

  selectedRestrictionIds: number[] = [];

  isNoneSelected(): boolean {
    return this.selectedRestrictionIds.includes(0);
  }

  selectRestriction(restriction: { id: number; name: string }) {
    const index = this.selectedRestrictionIds.indexOf(restriction.id);

    if (restriction.id == 0) {
      // Si la restriction "Aucune" est sélectionnée, vider la liste des autres restrictions
      this.selectedRestrictionIds = index === -1 ? [0] : [];
    } else {
      // Si une autre restriction est sélectionnée, gérer la liste normalement
      if (!this.selectedRestrictionIds.includes(restriction.id)) {
        this.selectedRestrictionIds.push(restriction.id);
      }
    }
  }

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.selectedRestrictionIds.map((id) => {
      const restriction = this.restrictions.find((r) => r.id == id);
      return restriction ? restriction.name : '';
    });

    return selectedRestrictionNames.join(', ');
  }

  // Ajoutez cette fonction pour vérifier si une restriction est sélectionnée
  isChecked(restriction: { id: number; name: string }): boolean {
    return this.selectedRestrictionIds.includes(restriction.id);
  }

  // Changer le fichier associé à une pièce
  onFileChange(file: File | undefined, index: number) {
    for (let i = 0; i < this.pieces.length; i++) {
      const pe = this.pieces[i];
      if (i === index) {
        pe.file = file;
        if (file && file.type.startsWith('image/')) {
          pe.src = URL.createObjectURL(file);
        }
      }
      this.pieces[i] = pe;
    }
  }

  openImageModal(imageSrc: any) {
    if (imageSrc) {
      this.imageSrc = imageSrc;
      $(`#openImageModal`).modal('show');
    }
  }

  // Enregistrer et naviguer vers la page de tableau de bord
  save() {
    this.fiche_medical = this.pieces[0].file;
    this.groupage_test = this.pieces[1].file;
    const formData = new FormData();
    formData.append('langue_id', this.langueSelected.id);
    formData.append('dossier_session_id', this.dossierSession.id);
    if (this.group) {
      formData.append('group_sanguin', this.group);
    }

    if (this.fiche_medical) {
      formData.append('fiche_medical', this.fiche_medical);
    }

    if (this.groupage_test) {
      formData.append('groupage_test', this.groupage_test);
    }
    const idsString = this.selectedRestrictionIds.length
      ? this.selectedRestrictionIds.join(',')
      : '';
    formData.append('restriction_medical', idsString);

    this.isloadingSave = true;
    this.post(formData);
  }

  private post(data: any) {
    this.errorHandler.startLoader();
    this.candidatService
      .editDossierCandidat(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloadingSave = false;
        })
      )
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        redirectTo('/dashboard', 2000);
        this.errorHandler.stopLoader();
      });
  }

  private _getLastDossierSession() {
    this.userHome = this.authService.storageService().get('auth');
    if (this.userHome) {
      this.errorHandler.startLoader();
      this.candidatService
        .getDossierSession()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.dossierSession = response.data;
          if (this.dossierSession) {
            const ficheMedical = this.dossierSession.fiche_medical;
            if (!!ficheMedical) {
            }
            const groupageTest =
              this.dossierSession?.dossierCandidat?.groupage_test;
            if (!!groupageTest) {
            }
            this.canEdit = this.dossierSession.state == 'init';
            let restriction_medical = [];
            try {
              restriction_medical = JSON.parse(
                this.dossierSession.restriction_medical
              );
            } catch (error) {
              restriction_medical = [];
            }

            if (Array.isArray(restriction_medical)) {
              for (let rId of restriction_medical) {
                if (typeof rId === 'string' && rId.trim().length == 0) {
                  continue;
                }
                const i = Number(rId);
                if (!isNaN(i)) {
                  this.selectRestriction({ id: Number(rId), name: '' });
                }
              }
            }
            const group_sanguin =
              this.dossierSession?.dossierCandidat?.group_sanguin;
            if (!!group_sanguin) {
              this.selectGroup(group_sanguin);
            }
            this.langue = this.dossierSession.langue_id;
            this.selectLangue(this.langue);
          } else {
            this.dossierSession = null;
            this.canEdit = false;
          }
          this.errorHandler.stopLoader();
        });
    }
  }
  ngOnInit(): void {
    this._getLangues();
    this._getRestrictions();
    this.todo = this.storage.get('todo');

    this._getLastDossierSession();
  }

  private _getLangues() {
    this.errorHandler.startLoader();
    this.candidatService
      .getLangues()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          // Filtrer les langues dont le statut est true
          this.langues = response.data.filter(
            (langue: any) => langue.status == true
          );
          this.errorHandler.stopLoader();
        }
      });
  }

  private _getRestrictions() {
    this.errorHandler.startLoader();
    this.candidatService
      .getRestrictions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.restrictions = [...this.restrictions, ...response.data];
          this.errorHandler.stopLoader();
        }
      });
  }

  selectLangue(langueId: any): void {
    this.langueSelected = this.langues.find((langue) => langue.id == langueId);
  }

  /**
   * Vérifier si la page d'informations sur la santé est valide
   * @returns
   */
  inforMedicalPageIsValid() {
    if (this.userHome.has_dossier_permis) {
      return (
        this.selectedRestrictionIds &&
        this.pieces.every((piece) => {
          if (piece.name == 'groupage') {
            return !isFile(piece.file);
          } else {
            return isFile(piece.file);
          }
        })
      );
    } else {
      return (
        is_array(this.selectedRestrictionIds) &&
        this.selectedRestrictionIds.length > 0
      );
    }
  }

  // Vérifier si la page de groupe sanguin est valide
  groupPageIsValid() {
    return this.group.length > 0;
  }
  asset(path: string) {
    return environment.candidat.asset + path;
  }
}
