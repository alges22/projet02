import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { TransactionResponse } from 'src/app/core/interfaces/transaction';
import { Candidat } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SettingService } from 'src/app/core/services/setting.service';
import { emitAlertEvent, isFile, toFormData } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-permis-international',
  templateUrl: './permis-international.component.html',
  styleUrls: ['./permis-international.component.scss'],
})
export class PermisInternationalComponent {
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
  permis_file: any;
  selectedCategories: any[] = [];
  selectedCategoryIds: any[] = [];
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
  isValidInfosDemande = false;
  quickvInfosDemande: any;
  email: any;
  num_permis: any;
  permisSelected: any;
  candidat: Candidat | null = null;
  user: any;
  permisThings: any;
  checkoutButtonOptions = {} as any;
  hasPermis: boolean = false;
  paymentApproved = false;
  permisInternationalId: any | null = null;
  qvForm: any = null;
  transaction: TransactionResponse | null = null;

  payment: {
    id: string | number;
    uuid: string;
  } | null = null;
  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private authService: AuthService
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

    // this.oldDemande();
    this._getCategoriePermis(() => {
      this.oldDemande();
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

  private onCheckoutComplete(resp: TransactionResponse) {
    if (resp.status == 'approved') {
      // this.download_url = response.data.url;
      // this.messageDownloadPermis = response.message;
      this.errorHandler.stopLoader();
      this.currentPage = 'paiement';
      this.gotoPage('paiement', event);
      this.transaction = resp;
      this.montant_payer = resp.amount;
      this.date_payment = resp.date_payment;
    } else {
      emitAlertEvent(
        "L'envoie de votre demande a échoué, le paiement n'a pu être effectué"
      );
    }
  }

  private savePaiement(form: FormData) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaiment(form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {});
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

  piecepermis = {
    label: 'Copie du permis de conduire (format image)',
    file: undefined as undefined | File,
    name: 'permis_file',
    content: 'Permis',
    src: '',
  };

  setActivePage(page: string) {
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  onFilePermisChange(file: File | undefined) {
    if (file) {
      this.piecepermis.file = file;
      if (file && file.type.startsWith('image/')) {
        this.piecepermis.src = URL.createObjectURL(file);
      }
    } else {
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
    const piecepermis = this.piecepermis.file;
    const formData = new FormData();
    formData.append('email', this.email);
    formData.append('num_permis', this.num_permis);
    formData.append('categorie_permis_ids', this.selectedCategoryIds.join(','));
    if (piecepermis) {
      formData.append('permis_file', piecepermis);
    }
    this.errorHandler.startLoader();
    this.candidatService
      .postPermisInternational(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.permisInternationalId = data.id;
        this.payment = {
          id: response.data.transactionId,
          uuid: response.data.uuid,
        };
        this.errorHandler.stopLoader();
      });
  }

  _update() {
    const piecepermis = this.piecepermis.file;
    const formData = new FormData();
    formData.append('email', this.email);
    formData.append('num_permis', this.num_permis);
    if (piecepermis) {
      formData.append('permis_file', piecepermis);
    }
    formData.append('rejet_id', this.rejetId || '');
    formData.append('categorie_permis_ids', this.selectedCategoryIds.join(','));
    this.errorHandler.startLoader();
    this.candidatService
      .updatePermisInternational(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.permisInternationalId = data.id;
        this.errorHandler.stopLoader();
        this.currentPage = 'paiement';
        this.gotoPage('paiement', event);
      });
  }

  private oldDemande() {
    if (this.rejetId) {
      this.candidatService
        .findPermisInternational(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.email = response.data.email;
          this.num_permis = response.data.num_permis;
          // this.gotoPage('recapitulatif', event);
          // this.fileValid = true;
          this.isValidInfosDemande = true;
          this.permis_file = response.data.permis_file;

          this.piecepermis.src = this.asset(response.data.permis_file);

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
          // this.errorHandler.stopLoader();
        });
    }
  }

  asset(path: string) {
    return environment.endpoints.asset + path;
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

  getTransaction(event: any) {
    this.onCheckoutComplete(event);
  }
}
