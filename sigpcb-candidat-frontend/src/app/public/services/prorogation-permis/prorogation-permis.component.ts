import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Candidat } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SettingService } from 'src/app/core/services/setting.service';
import { toFormData, emitAlertEvent, isFile } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-prorogation-permis',
  templateUrl: './prorogation-permis.component.html',
  styleUrls: ['./prorogation-permis.component.scss'],
})
export class ProrogationPermisComponent {
  currentPage = 'infos-demande';
  pages: any;
  categorypermis: any;
  categoriespermis: any;
  montant_payer: any;
  phone_payment: any;
  date_payment: any;
  download_url: any;
  messageDownloadPermis: any;
  permis: any;
  imageSrc: string = '';
  group: any;
  permis_file: any;
  group_file: any;
  fiche_file: any;
  pagesTemps = [
    {
      name: 'Infos de la demande',
      active: true,
      page: 'infos-demande',
    },
    {
      name: 'Summary',
      active: false,
      page: 'recapitulatif',
    },
    {
      name: 'Paiement',
      active: false,
      page: 'paiement',
    },
  ];
  groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
  piecepermis = {
    label: 'Copie du permis de conduire (format image)',
    file: undefined as undefined | File,
    name: 'permis_file',
    content: 'Permis',
    src: '',
  };
  piecegroup = {
    label: 'Pièce justificative du groupe sanguin (format image)',
    file: undefined as undefined | File,
    name: 'group_file',
    content: 'Groupe sanguin',
    src: '',
  };
  piecefiche = {
    label: 'Copie de fiche médicale (format image)',
    file: undefined as undefined | File,
    name: 'fiche_medicale',
    content: 'Fiche médicale',
    src: '',
  };
  isValidInfosDemande = false;
  quickvInfosDemande: any;
  email: any;
  num_permis: any;
  permisSelected: any;
  candidat: Candidat | null = null;
  user: any;
  checkoutButtonOptions = {} as any;
  hasPermis: boolean = false;
  paymentApproved = false;
  prorogationPermisId: any | null = null;
  transaction: {
    transactionId: number;
    montant: number;
    phone: string;
    status: string;
    operateur: string;
    prorogation_id: number;
    date_payment: string;
  } | null = null;
  @Input('rejetId') rejetId: string | null = null;
  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private authService: AuthService,
    private settingService: SettingService
  ) {}
  ngOnInit(): void {
    this.user = this.authService.storageService().get('auth');
    this._getCandidatWithNpi();
    // Traduire les prestationsTemp initialement
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });

    // this._getUserPermis();
    this.settingService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.checkoutButtonOptions = {
          public_key: environment.fedapay.key,
          environment: environment.fedapay.sandbox,
          transaction: {
            amount: response.data.prorogation_amount,
            description: "Paiement à l'ANaTT du Service ",
          },
          currency: {
            iso: 'XOF',
          },
          onComplete: this.onCheckoutComplete.bind(this),
        };
      });
    this.oldDemande();
  }

  ngAfterViewInit(): void {
    //@ts-ignore
    this.quickvInfosDemande = new QvForm('#infos-demande');
    this.quickvInfosDemande.init();

    this.quickvInfosDemande.onValidate((qvForm: any) => {
      this.isValidInfosDemande = qvForm.passes();
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

  private _getCandidatWithNpi() {
    if (this.user) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: this.user.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.candidat = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  onCheckoutComplete(resp: any) {
    if (this.prorogationPermisId) {
      const data = {
        transactionId: resp.transaction.id,
        montant: resp.transaction.amount,
        phone: resp.transaction.payment_method.number,
        status: resp.transaction.status,
        operateur: resp.transaction.mode,
        payment_for: 'prorogations-permis',
        prorogation_id: this.prorogationPermisId,
        date_payment: resp.transaction.payment_method.created_at,
      };

      if (this.paymentApproved) {
        const formData = toFormData(data);
        this.savePaimentProrogationPermis(formData);
      } else {
        // @ts-ignore
        const FedaPay = window['FedaPay'];
        if (resp.reason !== 'DIALOG DISMISSED') {
          if (data.status == 'approved') {
            this.transaction = data;
            this.paymentApproved = true;
            const formData = toFormData(data);
            this.savePaimentProrogationPermis(formData);
            this.montant_payer = resp.transaction.amount;
            this.phone_payment = resp.transaction.payment_method.number;
            this.date_payment = resp.transaction.payment_method.created_at;
          } else {
            emitAlertEvent(
              "L'envoie de votre demande a échoué, le paiement n'a pu être effectué"
            );
          }
        }
      }
    }
  }

  gotoPage(page: string, event?: Event) {
    event?.preventDefault();
    this.currentPage = page;
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  infosDemandeIsValid() {
    return (
      this.isValidInfosDemande &&
      this.filePermisValid() &&
      this.fileGroupValid() &&
      this.fileFicheValid()
    );
  }

  filePermisValid() {
    if (this.rejetId) {
      return true;
    } else {
      return isFile(this.piecepermis.file);
    }
  }

  fileGroupValid() {
    if (this.rejetId) {
      return true;
    } else {
      return isFile(this.piecegroup.file);
    }
  }

  fileFicheValid() {
    if (this.rejetId) {
      return true;
    } else {
      return isFile(this.piecefiche.file);
    }
  }

  setActivePage(page: string) {
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  payment() {
    // @ts-ignore
    const FedaPay = window['FedaPay'];
    if (FedaPay) {
      FedaPay.init(this.checkoutButtonOptions).open();
    }
  }

  onFilePermisChange(file: File | undefined) {
    if (file) {
      this.piecepermis.file = file;
      if (file && file.type.startsWith('image/')) {
        this.piecepermis.src = URL.createObjectURL(file);
      }
    }
  }

  onFileGroupChange(file: File | undefined) {
    if (file) {
      this.piecegroup.file = file;
      if (file && file.type.startsWith('image/')) {
        this.piecegroup.src = URL.createObjectURL(file);
      }
    }
  }

  onFileFicheChange(file: File | undefined) {
    if (file) {
      this.piecefiche.file = file;
      if (file && file.type.startsWith('image/')) {
        this.piecefiche.src = URL.createObjectURL(file);
      }
    }
  }

  openImageModal(imageSrc: any) {
    if (imageSrc) {
      this.imageSrc = imageSrc;
      $(`#openImageModal`).modal('show');
    }
  }

  save() {
    if (this.rejetId) {
      this._update();
    } else {
      this._save();
    }
  }

  _save() {
    if (!this.paymentApproved) {
      const piecepermis = this.piecepermis.file;
      const piecegroup = this.piecegroup.file;
      const piecefiche = this.piecefiche.file;
      const formData = new FormData();
      formData.append('email', this.email);
      formData.append('num_permis', this.num_permis);
      formData.append('group_sanguin', this.group);
      if (piecepermis) {
        formData.append('permis_file', piecepermis);
      }
      if (piecegroup) {
        formData.append('group_sanguin_file', piecegroup);
      }
      if (piecefiche) {
        formData.append('fiche_medical_file', piecefiche);
      }
      this.errorHandler.startLoader();
      this.candidatService
        .postProrogationPermis(formData)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          const data = response.data;
          this.prorogationPermisId = data.id;
          this.errorHandler.stopLoader();
          this.payment();
        });
    } else {
      const data = toFormData(this.transaction);
      this.savePaimentProrogationPermis(data);
    }
  }

  _update() {
    const piecepermis = this.piecepermis.file;
    const piecegroup = this.piecegroup.file;
    const piecefiche = this.piecefiche.file;
    const formData = new FormData();
    formData.append('email', this.email);
    formData.append('num_permis', this.num_permis);
    formData.append('group_sanguin', this.group);
    if (piecepermis) {
      formData.append('permis_file', piecepermis);
    }
    if (piecegroup) {
      formData.append('group_sanguin_file', piecegroup);
    }
    if (piecefiche) {
      formData.append('fiche_medical_file', piecefiche);
    }
    formData.append('rejet_id', this.rejetId || '');
    this.errorHandler.startLoader();
    this.candidatService
      .updateProrogationPermis(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.prorogationPermisId = data.id;
        this.errorHandler.stopLoader();
        this.currentPage = 'paiement';
        this.gotoPage('paiement', event);
      });
  }

  private savePaimentProrogationPermis(form: FormData) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentProrogationPermis(form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.download_url = response.data.url;
        this.messageDownloadPermis = response.message;
        this.errorHandler.stopLoader();
        this.currentPage = 'paiement';
        this.gotoPage('paiement', event);
      });
  }

  private oldDemande() {
    if (this.rejetId) {
      this.candidatService
        .findProrogation(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          console.log(response.data);
          this.email = response.data.email;
          this.num_permis = response.data.num_permis;
          this.group = response.data.group_sanguin;
          this.isValidInfosDemande = true;
          this.permis_file = response.data.permis_file;
          this.piecepermis.src = this.asset(response.data.permis_file);
          this.group_file = response.data.group_sanguin_file;
          this.piecegroup.src = this.asset(response.data.group_sanguin_file);
          this.fiche_file = response.data.fiche_medical_file;
          this.piecefiche.src = this.asset(response.data.fiche_medical_file);
        });
    }
  }

  asset(path: string) {
    return environment.endpoints.asset + path;
  }
}
