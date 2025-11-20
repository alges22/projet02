import { DatePipe } from '@angular/common';
import {
  AfterViewInit,
  ChangeDetectorRef,
  Component,
  ElementRef,
  OnChanges,
  OnInit,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { User } from 'src/app/core/interfaces/user.interface';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { AutoecoleService } from 'src/app/core/services/autoecole.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SharedServiceService } from 'src/app/core/services/shared-service.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { isFile } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-premier-inscription',
  templateUrl: './premier-inscription.component.html',
  styleUrls: ['./premier-inscription.component.scss'],
})
export class PremierInscriptionComponent implements AfterViewInit, OnInit {
  typePieceError: string | null = '';
  typePieceSelected = '';
  isloading = false;
  isloadingSave = false;
  auth: User | null = null;
  autoEcole: any;
  modalPermisPrealableId = 'openPermisPrealableModal';
  modalPermisExtensionId = 'openPermisExtensionModal';

  centreCompos = ['Cotonou', 'Parakou', 'Ouidah'];
  // centreCompos: any[] = [];
  centreComposSelected = '';
  langues: any[] = [];
  annexes: any[] = [];
  autoEcoles: any[] = [];
  autoEcolesDepart: any[] = [];
  annexeSelected: any = null;
  autoEcoleSelected = '';
  langueSelected: any;
  permisExtension: any;
  annexe = '';
  langue = '';
  imageSrc: string = '';
  inputId = 'openImageModal';
  fiche_medical: any;
  groupage_test: any;
  fichierpermisprealable: any;
  candidat_type: any;
  todo: any;
  type_examen: any;
  quickvInfosPermis: any;

  isValidInfosPermis = false;
  pages: any;
  isAutoEcole: boolean = false;
  permisList: any[] = [];
  pagesTemps = [
    {
      name: "Information about the driver's license",
      active: true,
      page: 'infos-sur-le-permis',
    },
    {
      name: 'Medical information',
      active: false,
      page: 'infos-medicales',
    },
    {
      name: 'Summary',
      active: false,
      page: 'recapitulatif',
    },
  ];
  // Le permis sélectionné
  permisSelected = {} as any;
  // La page actuellement active
  currentPage = 'infos-sur-le-permis';

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
  numpermis: any;
  nummatricule: any;
  permisprealable: any;

  pieces = [
    {
      label: 'Joindre votre fiche médicale (format image)',
      file: undefined as undefined | File,
      name: 'medical',
      content: 'Fiche médicale',
      src: '',
    },
    {
      label: 'Joindre votre test de groupage (format image)',
      file: undefined as undefined | File,
      name: 'groupage',
      content: 'Test de groupage',
      src: '',
    },
  ];

  piecepermisprealable = {
    label: 'Joindre votre permis préalable (format image)',
    file: undefined as undefined | File,
    name: 'permisprealable',
    content: 'Permis Préalable',
    src: '',
  };

  clearInput: number = 0;
  user: any;
  userHome: any;
  codeAutoEcole: any;
  serverResponseReceived: boolean = false;
  permis_prealable: any;

  previewI = '';
  permisPrealableInput = false;

  hasPermisPrealable: boolean = false;
  hasNotPermisPrealable: boolean = false;
  hasPermisPrealableNotValid: boolean = false;
  knownPermisPrealableCandidat: boolean = false;
  knownPermisPrealableCandidatMessage = '';
  hasExtensions: boolean = false;
  has_permis_extension: any;
  permis_extension_id = '';
  previousSelected: any;
  constructor(
    private router: Router,
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private annexeanattService: AnnexeAnattService,
    private autoecoleService: AutoecoleService,
    private authService: AuthService,
    private candidatService: CandidatService,
    private storage: StorageService,
    private datePipe: DatePipe,
    private sharedService: SharedServiceService,
    private changeDetector: ChangeDetectorRef
  ) {}

  // Sélectionner un groupe sanguin
  selectGroup(group: string) {
    this.group = group;
  }

  selectedRestrictionIds: number[] = [];

  isNoneSelected(): boolean {
    console.log(this.selectedRestrictionIds.includes(0));
    return this.selectedRestrictionIds.includes(0);
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

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.selectedRestrictionIds.map((id) => {
      const restriction = this.restrictions.find((r) => r.id === id);
      return restriction ? restriction.name : '';
    });

    return selectedRestrictionNames.join(', ');
  }

  // Ajoutez cette fonction pour vérifier si une restriction est sélectionnée
  isChecked(restriction: { id: number; name: string }): boolean {
    return this.selectedRestrictionIds.includes(restriction.id);
  }

  /**
   * Le permis sélectionné
   */

  onPermisChanged(selected: any) {
    this.permisSelected = selected;
    // this.permisSelected.firstSelected = false;
    this.hasNotPermisPrealable = false;
    this.hasPermisPrealableNotValid = false;
    if (this.permisSelected.permis_prealable) {
      this._permisPrealable(this.permisSelected);
    } else {
      this.permisPrealableInput = false;
    }

    if (this.permisSelected.extensions.length > 0) {
      this.permisExtension = this.permisList.find(
        (permis) =>
          permis.id ==
          this.permisSelected.extensions[0].categorie_permis_extensible_id
      );

      // if (this.previousSelected !== selected) {
      this.has_permis_extension = false;
      this.permis_extension_id = '';
      $(`#${this.modalPermisExtensionId}`).modal('show');
      // Mettez à jour le selected précédent avec le selected actuel
      //   this.previousSelected = selected;
      // }
    }

    // Forcer une détection manuelle des changements
    this.changeDetector.detectChanges();
  }

  hasExtensionButtuon() {
    if (this.has_permis_extension === 'true') {
      this.permis_extension_id = this.permisExtension.id;
    } else {
      this.permis_extension_id = '';
    }
    $(`#${this.modalPermisExtensionId}`).modal('hide');
  }

  _permisPrealable(permisselected: any) {
    this.errorHandler.startLoader();
    const param = {
      candidatId: this.userHome.id,
      permisPrealableId: permisselected.permis_prealable.id,
      permisPrealableDure: permisselected.permis_prealable_dure,
    };

    this.candidatService
      .checkCandidatPermisPrealable(param)
      .pipe(
        this.errorHandler.handleServerErrors((error: any) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        if (response.status) {
          if (response.statuscode == 200) {
          } else if (response.statuscode == 404) {
            this.permis_prealable = this.permisSelected.permis_prealable.name;
            this.hasPermisPrealable = false;
            $(`#${this.modalPermisPrealableId}`).modal('show');
            this.permisPrealableInput = false;
            this.knownPermisPrealableCandidat = false;
            this.hasNotPermisPrealable = true;
          } else if (response.statuscode == 422) {
            $(`#${this.modalPermisPrealableId}`).modal('show');
            this.knownPermisPrealableCandidat = true;
            this.hasPermisPrealableNotValid = true;
            this.knownPermisPrealableCandidatMessage = response.message;
          } else if (response.statuscode == 400) {
            this.errorHandler.emitAlert(
              response.message,
              'danger',
              'middle',
              true
            );
          }
        }
      });
  }

  togglePermisPrealable(event: any): void {
    const value = event.target.value;
    if (value == 'true') {
      this.hasPermisPrealable = true;
      this.hasNotPermisPrealable = false;
      $(`#${this.modalPermisPrealableId}`).modal('hide');
      this.permisPrealableInput = true;
      event.target.removeAttribute('checked');
      event.target.checked = false;
      $('#has_permis_prealable_no').click();
    } else {
      this.permisPrealableInput = true;
      this.hasPermisPrealable = false;
    }
  }

  /**
   * Sélection d'un type de pièce
   * @param target
   */
  onSelectedTypeEvent(target: any) {
    const selected = target.value as string;

    if (selected.length < 1) {
      this.typePieceError = 'Veuillez sélectionner un type de pièce';
    } else {
      this.typePieceError = null;
    }
    this.typePieceSelected = selected;
  }
  /**
   * Le code otp
   * @param data
   */
  onInputValid(data: { isValid: boolean; values: (number | null)[] }) {
    if (data.isValid) {
      this.codeAutoEcole = data.values.join('');
      this.isloading = true;
      this.autoecoleService
        .findByCode(this.codeAutoEcole)
        .pipe(
          this.errorHandler.handleServerErrors((error: any) => {
            this.isloading = false;
            this.codeAutoEcole = null;
            this.clearInput++;
          })
        )
        .subscribe((response) => {
          this.isloading = false;
          this.autoEcole = response.data;
          if (this.autoEcole)
            if (this.autoEcoleSelected == this.autoEcole.id) {
              this.candidat_type = this.autoEcole.type;
              this.isAutoEcole = true;
            } else {
              this.errorHandler.emitAlert(
                "Ce code ne correspond à l'auto école sélectionnée",
                'danger',
                'middle',
                true
              );
            }

          this.clearInput++;
        });
    } else {
      this.codeAutoEcole = null;
    }
  }

  private _getAutoEcocleWithCode(code: any) {
    this.isloading = true;
    this.autoecoleService
      .findByCode(code)
      .pipe(
        this.errorHandler.handleServerErrors((error: any) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.isloading = false;
        let message = response.message;
      });
  }

  // Aller à une page spécifique

  // gotoPage(page: string, event: Event) {
  //   event.preventDefault();
  //   if (
  //     page !== 'infos-medicales' ||
  //     (!this.hasPermisPrealableNotValid && !this.hasNotPermisPrealable)
  //   ) {
  //     this.currentPage = page;
  //     this.setActivePage(page);
  //   }
  // }

  gotoPage(page: string, event: Event) {
    event.preventDefault();

    if (page === 'infos-sur-le-permis') {
      this.isAutoEcole = false;
    }

    if (page === 'infos-medicales') {
      if (!this.inforPermisPageIsValid()) {
        this.errorHandler.emitAlert(
          'Veuillez renseigner tous les champs svp',
          'danger',
          'middle',
          true
        );
        return false;
      }
    }

    if (page === 'recapitulatif') {
      if (!this.inforMedicalPageIsValid()) {
        this.errorHandler.emitAlert(
          'Veuillez renseigner tous les champs svp',
          'danger',
          'middle',
          true
        );
        return false;
      }
    }

    if (
      page !== 'infos-medicales' ||
      (!this.hasPermisPrealableNotValid && !this.hasNotPermisPrealable)
    ) {
      this.currentPage = page;
      this.setActivePage(page);
    }
    return;
  }

  setActivePage(page: string) {
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
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

  onFilePermisPrealableChange(file: File | undefined) {
    if (file) {
      this.piecepermisprealable.file = file;
      if (file && file.type.startsWith('image/')) {
        this.piecepermisprealable.src = URL.createObjectURL(file);
      }
    }
  }

  openImageModal(imageSrc: any) {
    if (imageSrc) {
      this.imageSrc = imageSrc;
      $(`#openImageModal`).modal('show');
    }
  }

  // Enregistrer et naviguer vers la page de tableau de bord
  save(event: Event) {
    event.preventDefault();
    this.fiche_medical = this.pieces[0].file;
    this.groupage_test = this.pieces[1].file;
    this.fichierpermisprealable = this.piecepermisprealable.file;
    // this.candidat_type = this.storage.get('userType');
    this.type_examen = this.storage.get('type_examen');
    const formData = new FormData();
    formData.append('candidat_id', this.userHome.id);
    formData.append('annexe_id', this.annexeSelected);
    formData.append('auto_ecole_id', this.autoEcoleSelected);
    formData.append('categorie_permis_id', this.permisSelected.id);
    formData.append('categorie_permis_name', this.permisSelected.name);
    formData.append('langue_id', this.langueSelected.id);
    if (this.group) {
      formData.append('group_sanguin', this.group);
    }
    formData.append('code_autoecole', this.codeAutoEcole);

    if (this.fiche_medical) {
      formData.append('fiche_medical', this.fiche_medical);
    }
    if (this.permis_extension_id !== '') {
      formData.append('permis_extension_id', this.permis_extension_id);
    }
    if (this.permisSelected.permis_prealable) {
      formData.append(
        'permis_prealable_id',
        this.permisSelected.permis_prealable.id
      );
      formData.append(
        'permis_prealable_dure',
        this.permisSelected.permis_prealable_dure
      );
    }
    if (this.permisPrealableInput) {
      formData.append('num_permis', this.numpermis);
      formData.append('num_matricule', this.nummatricule);
      formData.append('fichier_permis_prealable', this.fichierpermisprealable);
    }
    if (this.groupage_test) {
      formData.append('groupage_test', this.groupage_test);
    }
    // formData.append('restriction_medical', this.restriction.id);
    // Ajoutez tous les IDs des restrictions sélectionnées à formData
    const idsString = this.selectedRestrictionIds.join(',');
    formData.append('restriction_medical', idsString);

    formData.append('candidat_type', this.candidat_type);
    formData.append('type_examen', this.type_examen);
    formData.append('has_dossier_permis', this.userHome.has_dossier_permis);
    formData.append('npi', this.userHome.npi);
    this.isloadingSave = true;
    this.post(formData);
  }

  private post(data: any) {
    this.candidatService
      .postDossierCandidatg(data)
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

  /**
   * Vérifier si toutes les pièces sont fournies
   * @returns
   */
  piecePageIsValid() {
    return (
      this.pieces.every((piece) => isFile(piece.file)) &&
      this.typePieceSelected.length > 0
    );
  }

  private _getCandidatWithNpi() {
    this.userHome = this.authService.storageService().get('auth');
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
  ngOnInit(): void {
    this._getLangues();
    this._getAnnexes();
    // this._getAutoEcoles();
    this._getRestrictions();
    this.todo = this.storage.get('todo');

    // Traduire les prestationsTemp initialement
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });

    this._getPermis();
    this._getCandidatWithNpi();
  }

  ngAfterViewInit(): void {
    //@ts-ignore
    this.quickvInfosPermis = new QvForm('#infos-sur-le-permis');
    this.quickvInfosPermis.init();

    this.quickvInfosPermis.onValidate((qvForm: any) => {
      this.isValidInfosPermis = qvForm.passes();
    });
  }

  translatePages(): void {
    const translationPromises = this.pagesTemps.map((page: any) => {
      return this.translate
        .get(page.name)
        .toPromise()
        .then((translation: string) => {
          return { ...page, name: translation };
        });
    });

    Promise.all(translationPromises).then((translatedPrestations: any[]) => {
      // Mettre à jour les prestationsTemp traduites
      this.pages = translatedPrestations;
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

  selectAnnexe(annexeId: any): void {
    // Obtenez l'annexe sélectionnée
    this.annexeSelected = annexeId;
    const selectedAnnexe = this.annexes.find((annexe) => annexe.id == annexeId);
    this.centreComposSelected = selectedAnnexe.name;

    if (annexeId) {
      this._getAutoEcoles(annexeId);
    } else {
      this.autoEcoles = [];
    }

    // if (selectedAnnexe) {
    //   // Filtrer les auto-écoles ayant des departement_id correspondants dans la propriété annexe_anatt_departements de l'annexe sélectionnée
    //   this.autoEcoles = this.autoEcolesDepart.filter((autoecole) =>
    //     selectedAnnexe.annexe_anatt_departements.some(
    //       (departement: any) =>
    //         autoecole.departement_id == departement.departement_id
    //     )
    //   );
    // } else {
    //   // Réinitialiser la liste des auto-écoles si aucune annexe n'est sélectionnée
    //   this.autoEcoles = [];
    // }
  }

  private _getPermis() {
    this.errorHandler.startLoader();
    this.candidatService
      .getCategoriesPermis()
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

  /**
   * Vérifier si la page d'informations sur le permis est valide
   * @returns
   */
  inforPermisPageIsValid() {
    return (
      this.isAutoEcole &&
      // this.isValidInfosPermis &&
      this.annexe &&
      this.langue &&
      this.autoEcoleSelected &&
      this.permisSelected !== undefined &&
      this.permisSelected !== null &&
      Object.keys(this.permisSelected).length > 0 &&
      typeof this.permisSelected === 'object' &&
      this.permisPrealableInputIsValid() &&
      !this.hasNotPermisPrealable &&
      !this.hasPermisPrealableNotValid
    );
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
          if (piece.name === 'groupage') {
            return !isFile(piece.file);
          } else {
            return isFile(piece.file);
          }
        })
      );
    } else {
      return (
        this.group.length > 0 &&
        this.selectedRestrictionIds &&
        this.pieces.every((piece) => isFile(piece.file))
      );
    }
  }

  permisPrealableInputIsValid() {
    if (this.permisPrealableInput) {
      return (
        this.numpermis &&
        this.nummatricule &&
        isFile(this.piecepermisprealable.file)
      );
    } else {
      return true;
    }
  }

  // Vérifier si la page de groupe sanguin est valide
  groupPageIsValid() {
    return this.group.length > 0;
  }
}
