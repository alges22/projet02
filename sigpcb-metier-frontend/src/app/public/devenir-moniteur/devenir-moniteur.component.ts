import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutementMoniteurService } from 'src/app/core/services/recrutement-moniteur.service';
import { isFile } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
import { TrivuleForm } from 'trivule';

@Component({
  selector: 'app-devenir-moniteur',
  templateUrl: './devenir-moniteur.component.html',
  styleUrls: ['./devenir-moniteur.component.scss'],
})
export class DevenirMoniteurComponent {
  pages: any;
  permis: any;
  npi: any;
  email: any;
  num_permis: any;
  imageSrc: string = '';
  permis_file: any;
  diplome_file: any;
  categoriespermis: any;
  selectedCategories: any[] = [];
  selectedCategoryIds: any[] = [];
  quickvInfosDemande: any;
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
  trivuleForm = new TrivuleForm({
    feedbackSelector: '.text-feedback',
  });

  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private recrutementMoniteurService: RecrutementMoniteurService,
    private recaptchaV3Service: ReCaptchaV3Service
  ) {}

  ngOnInit(): void {
    this.recaptchaV3Service
      .execute(environment.recaptcha_key)
      .subscribe((token) => {});
    this._getCategoriePermis();
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });
    this.trivuleForm.afterBinding(this.trivuleAfterBind.bind(this));
  }

  ngAfterViewInit(): void {
    this.trivuleForm.bind('#infos-demande');
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
  onFilePermisChange(file: File | undefined, type: any) {
    if (file) {
      if (type == 'permis') {
        this.piecepermis.file = file;
        if (file && file.type.startsWith('image/')) {
          this.piecepermis.src = URL.createObjectURL(file);
        }
      } else if (type == 'diplome') {
        this.piecediplome.file = file;
        if (file && file.type.startsWith('image/')) {
          this.piecediplome.src = URL.createObjectURL(file);
        }
      }
    } else {
    }
  }
  piecediplome = {
    label: 'Diplôme (format image)',
    file: undefined as undefined | File,
    name: 'diplome_file',
    content: 'Diplome',
    src: '',
  };

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
      this.trivuleForm.valid &&
      this.filePermisValid() &&
      this.fileDiplomeValid() &&
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

  fileDiplomeValid() {
    if (this.rejetId) {
      return true;
    } else {
      return isFile(this.piecediplome.file);
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
    const piecediplome = this.piecediplome.file;
    const formData = new FormData();
    formData.append('npi', this.npi);
    formData.append('email', this.email);
    formData.append('num_permis', this.num_permis);
    if (piecepermis) {
      formData.append('permis_file', piecepermis);
    }
    if (piecediplome) {
      formData.append('diplome_file', piecediplome);
    }
    formData.append('categorie_permis_ids', this.selectedCategoryIds.join(','));
    this.errorHandler.startLoader();
    this.recrutementMoniteurService
      .postRecrutementMoniteur(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.currentPage = 'completed';
        // this.permisInternationalId = data.id;
        this.errorHandler.stopLoader();
        // this.payment();
      });
    // } else {
    //   const data = toFormData(this.transaction);
    //   this.savePaiement(data);
    // }
  }
  trivuleAfterBind(form: TrivuleForm) {
    form.make({
      npi: {
        rules: 'required|digit:10',
        messages: {
          required: 'Veuillez saisir votre NPI',
          digit: 'Veuillez saisir un NPI valide',
        },
      },
      email: {
        rules: 'required|email',
        messages: {
          required: 'Veuillez saisir votre email',
          email: 'Veuillez saisir un email valide',
        },
      },
      num_permis: {
        rules: 'required|minlength:8|maxlength:25',
        messages: {
          required: 'Ce champ est obligatoire',
          minlength: 'Le numéro du permis semble être trop court',
          maxlength: 'Le numéro du permis semble être trop long',
          regex: "Le format du numéro du permis n'est pas pris en charge",
        },
      },
    });
  }
}
