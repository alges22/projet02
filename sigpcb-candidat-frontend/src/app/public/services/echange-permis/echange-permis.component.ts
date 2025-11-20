import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SettingService } from 'src/app/core/services/setting.service';
import { emitAlertEvent, isFile, toFormData } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-echange-permis',
  templateUrl: './echange-permis.component.html',
  styleUrls: ['./echange-permis.component.scss'],
})
export class EchangePermisComponent {
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
  authenticite_file: any;
  @Input('rejetId') rejetId: string | null = null;
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
  pieceauthenticite = {
    label: "Copie de l'authenticité (format image)",
    file: undefined as undefined | File,
    name: 'authenticite_file',
    content: 'Authenticité',
    src: '',
  };
  isValidInfosDemande = false;
  quickvInfosDemande: any;
  email: any;
  num_permis: any;
  permisSelected: any;
  candidat: any;
  user: any;
  permisThings: any;
  checkoutButtonOptions = {} as any;
  hasPermis: boolean = false;
  selectedCategories: any[] = [];
  date_delivrance_permis: any;
  ville_delivrance_permis: any;
  email_structure: any;
  paymentApproved = false;
  echangePermisId: any | null = null;
  transaction: {
    transactionId: number;
    montant: number;
    phone: string;
    status: string;
    operateur: string;
    echange_id: number;
    date_payment: string;
  } | null = null;
  selectedCategoryIds: any[] = [];
  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private authService: AuthService,
    private settingService: SettingService
  ) {}
  ngOnInit(): void {
    this.user = this.authService.storageService().get('auth');
    // this._getCategoriePermis();

    this._getCategoriePermis(() => {
      this.oldDemande();
    });
    this._getCandidatWithNpi();
    // Traduire les prestationsTemp initialement
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });

    this.settingService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.checkoutButtonOptions = {
          public_key: environment.fedapay.key,
          environment: environment.fedapay.sandbox,
          transaction: {
            amount: response.data.echange_amount,
            description: "Paiement à l'ANaTT du Service ",
          },
          currency: {
            iso: 'XOF',
          },
          onComplete: this.onCheckoutComplete.bind(this),
        };
      });
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

  private _getCategoriePermis(call: CallableFunction) {
    this.errorHandler.startLoader();
    this.candidatService
      .getCategoriesPermis()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categoriespermis = response.data;
        call();
        this.errorHandler.stopLoader();
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
    if (this.echangePermisId) {
      const data = {
        transactionId: resp.transaction.id,
        montant: resp.transaction.amount,
        phone: resp.transaction.payment_method.number,
        status: resp.transaction.status,
        operateur: resp.transaction.mode,
        payment_for: 'echange-permis',
        echange_id: this.echangePermisId,
        date_payment: resp.transaction.payment_method.created_at,
      };

      if (this.paymentApproved) {
        const formData = toFormData(data);
        this.savePaimentEchangePermis(formData);
      } else {
        // @ts-ignore
        const FedaPay = window['FedaPay'];
        if (resp.reason !== 'DIALOG DISMISSED') {
          if (data.status == 'approved') {
            this.transaction = data;
            this.paymentApproved = true;
            this.authService.storageService().store('atrt', data);
            const formData = toFormData(data);
            this.savePaimentEchangePermis(formData);
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
      this.fileAuthenticiteValid() &&
      this.selectedCategories.length > 0
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

  fileAuthenticiteValid() {
    if (this.rejetId) {
      return true;
    } else {
      return isFile(this.pieceauthenticite.file);
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

  onFileAuthenticiteChange(file: File | undefined) {
    if (file) {
      this.pieceauthenticite.file = file;
      if (file && file.type.startsWith('image/')) {
        this.pieceauthenticite.src = URL.createObjectURL(file);
      }
    }
  }

  openImageModal(imageSrc: any) {
    if (imageSrc) {
      this.imageSrc = imageSrc;
      $(`#openImageModal`).modal('show');
    }
  }
  selectCategorie(categorie: any) {
    const index = this.selectedCategories.findIndex(
      (cat) => cat.id === categorie.id
    );

    if (index === -1) {
      // Si la catégorie n'est pas encore sélectionnée, l'ajouter au tableau
      this.selectedCategories.push(categorie);
      this.selectedCategoryIds.push(categorie.id);
    } else {
      // Si la catégorie est déjà sélectionnée, la supprimer du tableau
      this.selectedCategories.splice(index, 1);
      const idIndex = this.selectedCategoryIds.indexOf(categorie.id);
      this.selectedCategoryIds.splice(idIndex, 1);
    }
  }
  isChecked(category: any): boolean {
    return this.selectedCategories.some((cat) => cat.id === category.id);
  }
  getCategoriesNames(): string {
    return this.selectedCategories.map((category) => category.name).join(', ');
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
      const pieceauthenticite = this.pieceauthenticite.file;
      const formData = new FormData();
      formData.append('email', this.email);
      formData.append('num_permis', this.num_permis);
      formData.append('delivrance_ville', this.ville_delivrance_permis);
      formData.append('group_sanguin', this.group);
      formData.append('delivrance_date', this.date_delivrance_permis);
      formData.append('structure_email', this.email_structure);
      formData.append(
        'categorie_permis_ids',
        this.selectedCategoryIds.join(',')
      );
      if (piecepermis) {
        formData.append('permis_file', piecepermis);
      }
      if (piecegroup) {
        formData.append('group_sanguin_file', piecegroup);
      }
      if (pieceauthenticite) {
        formData.append('authenticite_file', pieceauthenticite);
      }
      this.errorHandler.startLoader();
      this.candidatService
        .postEchangePermis(formData)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          const data = response.data;
          this.echangePermisId = data.id;
          this.errorHandler.stopLoader();
          this.payment();
        });
    } else {
      const data = toFormData(this.transaction);
      this.savePaimentEchangePermis(data);
    }
  }

  _update() {
    const piecepermis = this.piecepermis.file;
    const piecegroup = this.piecegroup.file;
    const pieceauthenticite = this.pieceauthenticite.file;
    const formData = new FormData();
    formData.append('email', this.email);
    formData.append('num_permis', this.num_permis);
    formData.append('delivrance_ville', this.ville_delivrance_permis);
    formData.append('group_sanguin', this.group);
    formData.append('delivrance_date', this.date_delivrance_permis);
    formData.append('structure_email', this.email_structure);
    formData.append('categorie_permis_ids', this.selectedCategoryIds.join(','));
    if (piecepermis) {
      formData.append('permis_file', piecepermis);
    }
    if (piecegroup) {
      formData.append('group_sanguin_file', piecegroup);
    }
    if (pieceauthenticite) {
      formData.append('authenticite_file', pieceauthenticite);
    }
    formData.append('rejet_id', this.rejetId || '');
    this.errorHandler.startLoader();
    this.candidatService
      .updateEchangePermis(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.echangePermisId = data.id;
        this.errorHandler.stopLoader();
        this.currentPage = 'paiement';
        this.gotoPage('paiement', event);
      });
  }

  private savePaimentEchangePermis(form: FormData) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentEchangePermis(form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.download_url = response.data.url;
        this.messageDownloadPermis = response.message;
        this.errorHandler.stopLoader();
        this.currentPage = 'paiement';
        this.gotoPage('paiement', event);
        this.authService.storageService().remove('atrt');
      });
  }

  private oldDemande() {
    this.errorHandler.startLoader();
    if (this.rejetId) {
      this.candidatService
        .findEchange(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          console.log(response.data);
          this.email = response.data.email;
          this.num_permis = response.data.num_permis;
          this.isValidInfosDemande = true;
          this.permis_file = response.data.permis_file;
          this.piecepermis.src = this.asset(response.data.permis_file);
          this.date_delivrance_permis = response.data.delivrance_date;
          this.ville_delivrance_permis = response.data.delivrance_ville;
          this.email_structure = response.data.structure_email;
          this.group = response.data.group_sanguin;
          this.group_file = response.data.group_sanguin_file;
          this.piecegroup.src = this.asset(response.data.group_sanguin_file);
          this.pieceauthenticite.src = this.asset(
            response.data.authenticite_file
          );
          this.authenticite_file = response.data.authenticite_file;
          const idsArray = JSON.parse(response.data.categorie_permis_ids)
            .split(',')
            .map((id: any) => parseInt(id, 10));
          this.selectedCategories = idsArray.map((id: any) => {
            const categorie = this.categoriespermis.find(
              (c: any) => c.id === id
            );
            return categorie;
          });
          this.selectedCategoryIds = this.selectedCategories.map(
            (categorie: any) => categorie.id
          );
          console.log(this.selectedCategoryIds);
          this.errorHandler.stopLoader();
        });
    }
  }

  asset(path: string) {
    return environment.endpoints.asset + path;
  }
}
