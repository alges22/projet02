import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { environment } from 'src/environments/environment';
import { emitAlertEvent, isFile, toFormData } from 'src/app/helpers/helpers';
import { RecrutementExaminateurService } from 'src/app/core/services/recrutement-examinateur.service';
import { ReCaptchaV3Service } from 'ng-recaptcha';

@Component({
  selector: 'app-devenir-examinateur',
  templateUrl: './devenir-examinateur.component.html',
  styleUrls: ['./devenir-examinateur.component.scss'],
})
export class DevenirExaminateurComponent {
  pages: any;
  annexes: any[] = [];
  annexe = '';
  permis: any;
  npi: any;
  email: any;
  num_permis: any;
  imageSrc: string = '';
  permis_file: any;
  categoriespermis: any;
  selectedCategories: any[] = [];
  selectedCategoryIds: any[] = [];
  isValidInfosDemande = false;
  quickvInfosDemande: any;
  annexeSelected: any;
  currentPage = 'infos-demande';
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
  ];
  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private annexeanattService: AnnexeAnattService,
    private candidatService: CandidatService,
    private recrutementExaminateurService: RecrutementExaminateurService,
    private recaptchaV3Service: ReCaptchaV3Service
  ) {}

  ngOnInit(): void {
    this.recaptchaV3Service
      .execute(environment.recaptcha_key)
      .subscribe((token) => {});
    this._getAnnexes();
    this._getCategoriePermis();
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });
    console.log(this.currentPage);
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
    // console.log(this.selectedCategories.some((cat) => cat.id === category.id));
    return this.selectedCategories.some((cat) => cat.id === category.id);
  }

  getCategoriesNames(): string {
    return this.selectedCategories.map((category) => category.name).join(', ');
  }

  selectAnnexe(annexeId: any): void {
    // Obtenez l'annexe sélectionnée
    const selectedAnnexe = this.annexes.find((annexe) => annexe.id == annexeId);
    this.annexeSelected = selectedAnnexe.name;
    console.log(this.annexeSelected);
  }

  private _getCategoriePermis() {
    this.errorHandler.startLoader();
    this.candidatService
      .getCategoriesPermis()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categoriespermis = response.data;
        this.errorHandler.stopLoader();
      });
  }

  piecepermis = {
    label: 'Permis de conduire (format image)',
    file: undefined as undefined | File,
    name: 'permis_file',
    content: 'Permis',
    src: '',
  };
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

  asset(path: string) {
    return environment.endpoints.asset + path;
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

  gotoPage(page: string, event?: Event) {
    console.log(this.annexe);
    event?.preventDefault();
    this.currentPage = page;
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  save() {
    if (this.rejetId) {
      // this._update();
    } else {
      this._save();
    }
  }

  _save() {
    // if (!this.paymentApproved) {
    const piecepermis = this.piecepermis.file;
    const formData = new FormData();
    formData.append('npi', this.npi);
    formData.append('email', this.email);
    formData.append('num_permis', this.num_permis);
    if (piecepermis) {
      formData.append('permis_file', piecepermis);
    }
    formData.append('categorie_permis_ids', this.selectedCategoryIds.join(','));
    formData.append('annexe_anatt_id', this.annexe);
    this.errorHandler.startLoader();
    this.recrutementExaminateurService
      .postRecrutementExaminateur(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.currentPage = 'completed';
        // this.permisInternationalId = data.id;
        this.errorHandler.stopLoader();
        // this.payment();
      });
  }
}
