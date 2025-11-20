import { DatePipe } from '@angular/common';
import { ChangeDetectorRef, Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { User } from 'src/app/core/interfaces/user.interface';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SharedServiceService } from 'src/app/core/services/shared-service.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { emitAlertEvent, isFile } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-edit-inscription',
  templateUrl: './edit-inscription.component.html',
  styleUrls: ['./edit-inscription.component.scss'],
})
export class EditInscriptionComponent {
  typePieceError: string | null = '';
  typePieceSelected = '';
  isloading = false;
  isloadingSave = false;
  auth: User | null = null;
  autoEcole: any;
  modalPermisPrealableId = 'openPermisPrealableModal';
  modalPermisExtensionId = 'openPermisExtensionModal';
  modalLateSession = 'openLateSessionModal';
  download_url: any;
  selectedRestrictionIds: number[] = [];
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
  dossier_session_id: any;
  dossier_candidat: any;
  dossier_session: any;
  sessions: any[] = [];
  session = '';
  modalId = 'openModalSessionPayment';
  montantButton: any;
  paiement_success: boolean = false;
  montant_payer: any;
  phone_payment: any;
  date_payment: any;
  checkoutButtonOptions = {} as any;
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

  isValidInfosPermis = false;
  assetLink = environment.candidat.asset;
  // pages: any;
  isAutoEcole: boolean = false;
  permisList: any[] = [];
  pages = [
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
  permisSelected: CategoryPermis | null = null;
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
      label: 'Joindre votre fiche médicale',
      file: undefined as undefined | File,
      name: 'medical',
      content: 'Fiche médicale',
      src: '',
    },
    {
      label: 'Joindre votre test de groupage',
      file: undefined as undefined | File,
      name: 'groupage',
      content: 'Test de groupage',
      src: '',
    },
  ];

  piecepermisprealable = {
    label: 'Joindre votre permis préalable',
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
    private readonly router: Router,
    private readonly translate: TranslateService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly annexeanattService: AnnexeAnattService,
    private readonly authService: AuthService,
    private readonly candidatService: CandidatService,
    private readonly storage: StorageService,
    private readonly changeDetector: ChangeDetectorRef,
    private readonly route: ActivatedRoute
  ) {}

  // Sélectionner un groupe sanguin
  selectGroup(group: string) {
    this.group = group;
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
  isChecked(restriction: { id: number; name: string }): boolean {
    return this.selectedRestrictionIds.includes(restriction.id);
  }
  isNoneSelected(): boolean {
    return this.selectedRestrictionIds.includes(0);
  }
  onPermisChanged(selected: any) {
    this.permisSelected = selected;
    this.hasNotPermisPrealable = false;
    this.hasPermisPrealableNotValid = false;
    if (this.permisSelected?.permis_prealable) {
      this._permisPrealable(this.permisSelected);
    } else {
      this.permisPrealableInput = false;
    }

    //@ts-ignore
    if (this.permisSelected.extensions.length > 0) {
      this.permisExtension = this.permisList.find(
        //@ts-ignore
        (permis) => permis.id == this.permisSelected.extensions[0].id
      );
      this.has_permis_extension = false;
      this.permis_extension_id = '';
      $(`#${this.modalPermisExtensionId}`).modal('show');
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
            this.permis_prealable = this.permisSelected?.permis_prealable.name;
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

  gotoPage(page: string, event?: Event) {
    event?.preventDefault();
    if (
      page !== 'infos-medicales' ||
      (!this.hasPermisPrealableNotValid && !this.hasNotPermisPrealable)
    ) {
      this.currentPage = page;
      this.setActivePage(page);
    }
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
    if (this.pieces[0].file) this.fiche_medical = this.pieces[0].file;
    if (this.pieces[1].file) this.groupage_test = this.pieces[1].file;
    const formData = new FormData();
    formData.append('id', this.dossier_session_id);
    if (this.group) {
      formData.append('group_sanguin', this.group);
    }

    if (this.fiche_medical) {
      formData.append('fiche_medical', this.fiche_medical);
    }
    if (this.groupage_test) {
      formData.append('groupage_test', this.groupage_test);
    }
    const idsString = this.selectedRestrictionIds.join(',');
    formData.append('restriction_medical', idsString);
    this.isloadingSave = true;
    this.update(formData);
  }

  private update(data: any) {
    this.candidatService
      .updateDossierCandidat(data, this.dossier_session_id)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloadingSave = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.isloadingSave = false;
          if (response.statuscode == 200) {
            this.currentPage = 'completed';
          } else if (response.statuscode == 201) {
            this.currentPage = 'submit-late';
            this.dossier_session = response.data.newDossierSession;
          }
        }
      });
  }

  closeDossier(dossier_id: number) {
    this.errorHandler.startLoader();
    const data = {
      dossier_id: dossier_id,
    };
    this._closeDossier(data);
  }

  private _closeDossier(data: any) {
    this.candidatService
      .closeDossier(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        if (response.status) {
          emitAlertEvent(
            'Le dossier a été bien fermé, vous pouvez vous préinscrire',
            'success'
          );
          setTimeout(() => {
            this.router.navigate(['/services/suivre-dossier/']);
          }, 5000);
        }
      });
  }

  isSessionDisabled(session: any): boolean {
    const today = new Date();
    const dateGestionRejet = new Date(session.fin_gestion_rejet_at);

    dateGestionRejet.setDate(dateGestionRejet.getDate() - 1);

    // Si la date de gestion de rejet est dépassée par rapport à la date actuelle, on désactive la session
    return dateGestionRejet < today;
  }

  sessionPaymentModal() {
    $(`#${this.modalId}`).modal('show');
  }

  saveSessionExpire() {
    if (this.session) {
      $(`#${this.modalId}`).modal('hide');
      const data = {
        dossier_candidat_id: this.dossier_candidat.id,
        examen_id: parseInt(this.session),
      };
      //@ts-ignore
      data.dossier_session_id = this.dossier_session.id;
      this._saveSessionExpire(data);
    }
  }

  payment() {
    if (this.session) {
      $(`#${this.modalId}`).modal('hide');
      // @ts-ignore
      const FedaPay = window['FedaPay'];
      if (FedaPay) {
        FedaPay.init(this.checkoutButtonOptions).open();
      }
    }
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
    this._getSessions();
    this._getRestrictions();
    this.todo = this.storage.get('todo');

    // Traduire les prestationsTemp initialement
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });

    this._getCandidatWithNpi();
    event: Event;
    this.route.params.subscribe((params) => {
      const id = params['id'];
      this.dossier_session_id = id;
      if (id) {
        this._getPermisPromise().then(() => {
          return this._getDossierCandidatwithSessionId(id);
        });
        this.gotoPage('recapitulatif', event);
        this.pages[1].active = true;
      } else {
        this._getPermis();
      }
    });

    this.checkoutButtonOptions = {
      transaction: {
        amount: 100,
        description: "Paiement à l'ANaTT du Service ",
      },
      currency: {
        iso: 'XOF',
      },
      onComplete: this.onCheckoutComplete.bind(this),
    };
  }

  onCheckoutComplete(resp: any) {
    if (resp.reason !== 'DIALOG DISMISSED') {
      if (resp.transaction.status === 'approved') {
        const data = {
          agregateur: 'fedapay',
          description: resp.transaction.description,
          transaction_id: resp.transaction.id,
          reference: resp.transaction.reference,
          mode: resp.transaction.mode,
          operation: resp.transaction.operation,
          transaction_key: resp.transaction.transaction_key,
          montant: resp.transaction.amount,
          phone_payment: resp.transaction.payment_method.number,
          ref_operateur: resp.transaction.transaction_key,
          moyen_payment: 'momo',
          status: resp.transaction.status,
          date_payment: resp.transaction.payment_method.created_at,
          dossier_candidat_id: this.dossier_candidat.id,
          session_id: this.session,
        };

        //@ts-ignore
        data.dossier_session_id = this.dossier_session.id;
        this._savePaiementExpire(data);
        this.currentPage = 'facture';
        // this.paiement_success = true;
        this.montant_payer = resp.transaction.amount;
        this.phone_payment = resp.transaction.payment_method.number;
        this.date_payment = resp.transaction.payment_method.created_at;
      }
    }
  }

  private _saveSessionExpire(data: any) {
    this.errorHandler.startLoader();
    this.candidatService
      .updateSession(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        // this.paiement_success = true;

        // this.download_url = response.data.url;
        this.errorHandler.stopLoader();
        this.currentPage = 'completed';
      });
    this.dossier_session_id = '';
  }

  private _savePaiementExpire(data: any) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentCandidatExpire(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.paiement_success = true;
        this.download_url = response.data.url;
        this.errorHandler.stopLoader();
      });
    this.dossier_session_id = '';
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
  }

  private _getPermisPromise() {
    return new Promise((resolve, reject) => {
      this.errorHandler.startLoader();
      this.candidatService
        .getCategoriesPermis()
        .pipe(
          this.errorHandler.handleServerErrors((error: any) => {
            this.errorHandler.stopLoader();
            reject(error);
          })
        )
        .subscribe((response) => {
          this.permisList = response.data;
          resolve(response);
          this.errorHandler.stopLoader();
        });
    });
  }

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.selectedRestrictionIds.map((id) => {
      const restriction = this.restrictions.find((r) => r.id == id);
      return restriction ? restriction.name : '';
    });

    return selectedRestrictionNames.join(', ');
  }

  private _getDossierCandidatwithSessionId(session_id: number) {
    this.errorHandler.startLoader();
    this.candidatService
      .getDossierCandidatwithSessionId(session_id)
      .pipe(
        this.errorHandler.handleServerErrors((error: any) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        if (
          response.data &&
          response.data.dossier_candidat &&
          response.data.dossier_session
        ) {
          this.dossier_candidat = response.data.dossier_candidat;
          // this.dossier_session = response.data.dossier_session;
          this.permisSelected = this.permisList.find(
            (permis) =>
              permis.id == response.data.dossier_candidat.categorie_permis_id
          );

          this.annexe = response.data.dossier_session.annexe_id;
          this.langue = response.data.dossier_session.langue_id;
          this.langueSelected = this.langues.find(
            (langue) => langue.id == response.data.dossier_session.langue_id
          );
          this.selectAnnexe(this.annexe);
          this.autoEcoleSelected = response.data.dossier_session.auto_ecole_id;

          this.selectedRestrictionIds = JSON.parse(
            response.data.dossier_session.restriction_medical
          ).map((id: any) => parseInt(id, 10));

          this.restriction = this.restrictions.find(
            (restriction) =>
              restriction.id ==
              response.data.dossier_session.restriction_medical
          );
          this.group = response.data.dossier_candidat.group_sanguin;
          this.groupage_test = response.data.dossier_candidat.groupage_test;
          this.fiche_medical = response.data.dossier_session.fiche_medical;
          this.pieces[0].src =
            this.assetLink + '' + response.data.dossier_session.fiche_medical;
          this.pieces[1].src =
            this.assetLink + '' + response.data.dossier_candidat.groupage_test;
          if (response.data.dossier_candidat.last_ancien_permis) {
            this.permisPrealableInput = true;
            this.permis_prealable = this.permisList.find(
              (permis) =>
                permis.id ==
                response.data.dossier_candidat.last_ancien_permis
                  .categorie_permis_id
            );
            this.nummatricule =
              response.data.dossier_candidat.last_ancien_permis.num_matricule;
            this.numpermis =
              response.data.dossier_candidat.last_ancien_permis.num_permis;
            this.piecepermisprealable.src =
              this.assetLink +
              '' +
              response.data.dossier_candidat.last_ancien_permis
                .fichier_permis_prealable;
          }

          this.isValidInfosPermis = true;
        }

        this.errorHandler.stopLoader();
      });
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

  private _getSessions() {
    this.errorHandler.startLoader();
    this.candidatService
      .getSessions()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.sessions = response.data;
        this.errorHandler.stopLoader();
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
          this.autoEcole = this.autoEcoles.find(
            (autoecole) => autoecole.id == this.autoEcoleSelected
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
      this.isValidInfosPermis &&
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
    return (
      this.group.length > 0 &&
      this.restriction &&
      this.pieces.every((piece) => isFile(piece.file))
    );
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
