import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutementMoniteurService } from 'src/app/core/services/recrutement-moniteur.service';
import { isFile } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-edit-devenir-moniteur',
  templateUrl: './edit-devenir-moniteur.component.html',
  styleUrls: ['./edit-devenir-moniteur.component.scss'],
})
export class EditDevenirMoniteurComponent {
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
  isValidInfosDemande = false;
  quickvInfosDemande: any;
  currentPage = 'infos-demande';
  rejetId: string | null = null;
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
    private candidatService: CandidatService,
    private recrutementMoniteurService: RecrutementMoniteurService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this._getCategoriePermis(() => {
      this.oldDemande();
    });
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });

    this.route.paramMap.subscribe((params) => {
      this.rejetId = params.get('rejetId');
      console.log(this.rejetId);
    });
  }

  private oldDemande() {
    this.errorHandler.startLoader();
    if (this.rejetId) {
      this.recrutementMoniteurService
        .findDemande(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.npi = response.data.npi;
          this.email = response.data.email;
          this.num_permis = response.data.num_permis;
          this.isValidInfosDemande = true;
          this.permis_file = response.data.permis_file;
          this.diplome_file = response.data.diplome_file;
          this.piecediplome.src = this.asset(this.diplome_file);
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
          this.errorHandler.stopLoader();
        });
    }
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

  piecepermis = {
    label: 'Permis de conduire (format image)',
    file: undefined as undefined | File,
    name: 'permis_file',
    content: 'Permis',
    src: '',
  };
  piecediplome = {
    label: 'Diplôme',
    file: undefined as undefined | File,
    name: 'diplome_file',
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
  onFileDiplomeChange(file: File | undefined) {
    if (file) {
      this.piecediplome.file = file;
      if (file.type.startsWith('image/')) {
        this.piecediplome.src = URL.createObjectURL(file);
      }
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
    event?.preventDefault();
    this.currentPage = page;
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  save() {
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
    formData.append('rejet_id', this.rejetId || '');
    this.errorHandler.startLoader();
    this.recrutementMoniteurService
      .updateRecrutementMoniteur(formData)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        // this.echangePermisId = data.id;
        this.errorHandler.stopLoader();
        this.currentPage = 'completed';
        // this.gotoPage('completed', event);
      });
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
}
