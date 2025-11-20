import { Component } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Langue } from 'src/app/core/interfaces/langue';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-inscription-conduite',
  templateUrl: './inscription-conduite.component.html',
  styleUrls: ['./inscription-conduite.component.scss'],
})
export class InscriptionConduiteComponent {
  autoEcoles: any[] = [];
  centreComposSelected = '';
  annexes: any[] = [];
  langueSelected: Langue | undefined = undefined;

  isloadingSave = false;
  langues: Langue[] = [];
  langue = '';
  permisList: any[] = [];
  dossier_session: any;
  userHome: { id: number; npi: string; has_dossier_permis: boolean } | null =
    null;
  user: any;
  annexe = '';
  autoEcoleId = '';
  isloading = false;
  // Le permis sélectionné
  permisSelected: CategoryPermis | null = null;
  categorypermis = '';
  isAutoEcole: boolean = false;
  dossier_id: any;
  candidat_type = 'civil';
  nom_permis: string | null = null;
  groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
  group = '';
  restrictions: any[] = [
    {
      id: 0,
      name: 'Aucune',
      description: null,
      created_at: null,
      updated_at: null,
    },
  ];
  selectedRestrictionIds: number[] = [];
  groupFile: File | null = null;
  medicalFile: File | null = null;
  private _groupFile: string | null = null;
  private _mediaFile: string | null = null;
  constructor(
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly cpService: CategoryPermisService,
    private readonly candidatService: CandidatService,
    private readonly annexeanattService: AnnexeAnattService,
    private readonly storage: StorageService,
    private readonly authService: AuthService
  ) {}
  currentPage = 'infos' as 'infos' | 'recapitulatif' | 'completed';

  ngOnInit(): void {
    this._getUserConnected();
    this._getAnnexes();
    this._getRestrictions();
    this._getPermis();
    this._getCandidatWithNpi();

    this._getLangues();
    this._getDossierSession(() => {
      if (!this.is_external) {
        this._getLastDossierCandidatWithID();
      }
    });
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
            (langue: any) => langue.status === true
          );
          this.errorHandler.stopLoader();
        }
      });
  }
  get is_external() {
    const ds = this.dossier_session;
    if (ds) {
      return ds.resultat_conduite !== 'failed';
    }
    return !this.dossier_session;
  }
  private _getUserConnected() {
    this.userHome = this.authService.storageService().get('auth');
  }

  private _getPermis() {
    this.errorHandler.startLoader();
    this.cpService
      .all()
      .pipe(
        this.errorHandler.handleServerErrors((error: any) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        this.permisList = response.data;
        this.errorHandler.stopLoader();
      });
  }
  private _getDossierSession(call: CallableFunction) {
    this.errorHandler.startLoader();
    this.candidatService
      .getDossierSession()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          // Filtrer les langues dont le statut est true
          this.dossier_session = response.data;
          call();
          this.errorHandler.stopLoader();
        }
      });
  }

  private _getLastDossierCandidatWithID() {
    if (this.userHome) {
      this.errorHandler.startLoader();
      this.candidatService
        .getLastDossierCandidatWithId()
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          if (
            response.data &&
            response.data.dossier &&
            response.data.dossier_session
          ) {
            this.permisSelected = this.permisList.find(
              (permis) => permis.id == response.data.dossier.categorie_permis_id
            );

            this.annexe = response.data.dossier_session.annexe_id;
            this.dossier_id = response.data.dossier.id;
            this.nom_permis = response.data.nom_permis;
            this.selectAnnexe(this.annexe);
            this.autoEcoleId = response.data.dossier_session.auto_ecole_id;
          }

          this.errorHandler.stopLoader();
        });
    }
  }
  selectGroup(group: string) {
    this.group = group;
  }

  private _getCandidatWithNpi() {
    if (this.userHome) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: this.userHome.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.user = response.data;

          this.errorHandler.stopLoader();
        });
    }
  }
  selectLangue(langueId: any): void {
    this.langueSelected = this.langues.find((langue) => langue.id == langueId);
  }
  private _getAnnexes() {
    this.errorHandler.startLoader();
    this.annexeanattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.annexes = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }
  selectAnnexe(annexeId: any): void {
    this.autoEcoleId = '';
    this.autoEcoles = [];
    // Obtenez l'annexe sélectionnée
    if (annexeId) {
      this._getAutoEcoles(annexeId);
    }
  }
  selectRestriction(restriction: { id: number; name: string }) {
    const index = this.selectedRestrictionIds.indexOf(restriction.id);

    if (restriction.id === 0) {
      // Si la restriction "Aucune" est sélectionnée, vider la liste des autres restrictions
      this.selectedRestrictionIds = index === -1 ? [0] : [];
    } else {
      // Si une autre restriction est sélectionnée, gérer la liste normalement
      if (index === -1) {
        this.selectedRestrictionIds.push(restriction.id);
      } else {
        this.selectedRestrictionIds.splice(index, 1);
      }
    }
  }

  get autoEcole() {
    if (String(this.autoEcoleId).length < 1) {
      return null;
    }
    return this.autoEcoles.find((ae) => ae.id == this.autoEcoleId) ?? null;
  }

  private _getAutoEcoles(annexeId: number) {
    this.errorHandler.startLoader();
    this.candidatService
      .getAutoEcoles(-1, 'all', annexeId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.autoEcoles = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }
  get groupFileUrl() {
    if (this._groupFile) {
      return this._groupFile;
    }
    if (!this.groupFile) {
      return null;
    }
    this._groupFile = URL.createObjectURL(this.groupFile);
    return this._groupFile;
  }

  get medicalFileUrl() {
    if (!this.medicalFile) {
      return null;
    }
    if (this._mediaFile) {
      return this._mediaFile;
    }

    this._mediaFile = URL.createObjectURL(this.medicalFile);
    return this._mediaFile;
  }
  /**
   * Vérifier si la page d'informations sur le permis est valide
   * @returns
   */
  inforPermisPageIsValid() {
    return !!this.autoEcole;
  }

  goto(page: 'infos' | 'recapitulatif' | 'completed', event: Event) {
    this.currentPage = page;
  }
  save(event: Event) {
    const formData = new FormData();
    formData.append('examen_type', 'conduite');
    formData.append('auto_ecole_id', this.autoEcoleId);
    event.preventDefault();

    this.candidat_type = this.storage.get('userType') ?? this.candidat_type;
    if (!this.is_external) {
      formData.append('dossier_candidat_id', this.dossier_id);
      formData.append('categorie_permis_id', this.permisSelected?.id as any);
      formData.append('annexe_anatt_id', this.annexe);

      if (this.nom_permis) {
        formData.append('nom_permis', this.permisSelected?.name as any);
      }

      this.isloadingSave = true;
      this.post(formData);
    } else {
      this.saveNewDatta(formData);
    }
  }
  saveNewDatta(formData: FormData) {
    if (this.userHome) {
      formData.append('candidat_id', this.userHome.id.toString());
      formData.append('npi', this.userHome.npi);
    }
    formData.append('code_autoecole', this.autoEcole.code);
    formData.append('annexe_anatt_id', this.annexe);
    formData.append('annexe_id', this.annexe);
    formData.append('categorie_permis_id', this.categorypermis);
    formData.append('categorie_permis_name', this.permisName);
    formData.append('langue_id', this.langue);
    formData.append('is_external', this.is_external as any);
    if (this.group) {
      formData.append('group_sanguin', this.group);
    }

    if (this.medicalFile) {
      formData.append('fiche_medical', this.medicalFile);
    }

    if (this.groupFile) {
      formData.append('groupage_test', this.groupFile);
    }
    const idsString = this.selectedRestrictionIds.length
      ? this.selectedRestrictionIds.join(',')
      : '';
    formData.append('restriction_medical', idsString);

    formData.append('candidat_type', this.candidat_type);
    formData.append('type_examen', 'conduite');

    this.isloadingSave = true;
    this.candidatService
      .postInscriptionReconduit(formData)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloadingSave = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.authService.storageService().store('auth', {
            id: response.data.user.id,
            npi: response.data.user.npi,
            has_dossier_permis: response.data.user.has_dossier_permi,
          });
          this.currentPage = 'completed';
          this.isloadingSave = false;
        }
      });
  }

  private post(data: any) {
    this.candidatService
      .postParcoursCandidat(data)
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
  isChecked(restriction: { id: number; name: string }): boolean {
    return this.selectedRestrictionIds.includes(restriction.id);
  }
  isNoneSelected(): boolean {
    return this.selectedRestrictionIds.includes(0);
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

  onGroupFileChange(file: File | undefined) {
    this.groupFile = file ?? null;
    this._groupFile = null;
  }

  onMedicalFileChange(file: File | undefined) {
    this.medicalFile = file ?? null;
    this._mediaFile = null;
  }
  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.selectedRestrictionIds.map((id) => {
      const restriction = this.restrictions.find((r) => r.id === id);
      return restriction ? restriction.name : '';
    });

    return selectedRestrictionNames.join(', ');
  }

  get permisName() {
    if (this.is_external) {
      return (
        this.permisList.find((perm) => perm.id == this.categorypermis)?.name ??
        null
      );
    }
    return this.nom_permis;
  }

  get langueName() {
    return this.langues.find((lang) => lang.id?.toString() == this.langue)
      ? this.langueSelected?.name
      : null;
  }
  get annexedName() {
    return (
      this.annexes.find((annexe) => annexe.id == this.annexe)?.name ?? null
    );
  }
}
