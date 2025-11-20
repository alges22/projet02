import {
  AfterViewChecked,
  AfterViewInit,
  Component,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { TrancheAge } from 'src/app/core/interfaces/tranche-age';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { TrancheAgeService } from 'src/app/core/services/tranche-age.service';
import { emitAlertEvent, redirectTo } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-add-permis',
  templateUrl: './add-permis.component.html',
  styleUrls: ['./add-permis.component.scss'],
})
export class AddPermisComponent
  implements OnInit, AfterViewInit, AfterViewChecked, OnDestroy
{
  constructor(
    private categoryPermisService: CategoryPermisService,
    private trancheAgeService: TrancheAgeService,
    private errorHandler: HttpErrorHandlerService,
    private route: ActivatedRoute
  ) {}

  private basicQvForm: any;
  private tranchQvForm: any;

  page_title = 'Ajouter une catégorie';

  // Ce décorateur permettra que la catégorie peut être récupérée de la liste
  category = {} as CategoryPermis;

  onLoading = false;
  /** Les extensions en local */
  extensions: {
    id?: number;
    categorie_permis_id?: number;
    categorie_permis_extensible_id?: number;
    name: string;
  }[] = [];
  /**
   * Uniquement la liste des catégories de permis qui sont des extensions
   */
  lesExtensions: CategoryPermis[] = [];
  /** Liste de toutes les catégories */
  categories: CategoryPermis[] = [];
  /** Une tranche d'age en cours de création ou de modification */
  tranche: TrancheAge = { age_min: null, age_max: null };
  /**
   * Liste des tranches d'ages
   */
  tranches: TrancheAge[] = [];

  is_extension = false;

  has_extensions = false;
  has_tranche = false;

  /**
   * Catégorie extension sélectionnée
   */
  catExtSelected = '';

  accordionIndex: number | null = null;

  newTranches: TrancheAge[] = [];
  /** Ceci permettra de savoir s'il s'agit d'une édition ou pas */
  editPage = false;
  /**
   * Permetra de savoir si une tranche d'âge a été soumise à la modification
   */
  editTranche = false;

  addNewTrancheAge = true;

  can_remove_pm_ext = false;
  pm_ext_to_remove: string | null = null;

  /** Si les formulaires ont subt de modification */
  private basicFormHasChange = false;

  private extensionFormValid = false;

  trancheToDelete = {} as TrancheAge;
  onPostingTranches = false;

  forms = {
    basicIsValid: false,
    trancheIsValid: false,
    enableBasicOnEdit: false,
    enableTrancheOnEdit: false,
    trncheAgeStateChanged: false,
  };

  permis_prealable_id: number | null = null;

  ngOnInit(): void {
    const paramValue = this.route.snapshot.paramMap.get('id') as any;
    this.editPage = paramValue !== null;

    if (this.editPage) {
      this.forms.enableBasicOnEdit = true;
      this.forms.enableTrancheOnEdit = true;
    }
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        this.lesExtensions = this.categories.filter((cat) => {
          return cat.is_extension;
        });

        const cat = this.categories.find((cat) => cat.id == paramValue);
        if (cat) {
          this.category = cat;
          this.page_title = `Modfier la catégoire de permis: <b> ${this.category.name} </b>`;
          if (cat.permis_prealable) {
            this.permis_prealable_id = cat.permis_prealable.id ?? null;
          }
        }

        if (paramValue && !cat) {
          emitAlertEvent(
            "<div class='mb-3 fw-semibold'>Catégorie de permis introuvable</div> Désolé! Cette catégoie de permis n'existe pas ou a été retirée déjà",
            'danger',
            'middle',
            true
          );

          return;
        }

        this.resetData();
        //Met ajout la catégorie
        this.setTrancheageFromServer();
      });
  }
  /** Enregistre ou modifie les informations de bases d'une catégorie */
  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    this.category.is_extension = this.is_extension;
    // if (this.is_extension) this.category.montant_extension = this.montant_extension;
    console.log(this.category);
    /** Mets à jour les extensions avant  dans la DB */
    this.category.extensions = this.extensions.map((ext) => {
      const cat = this.lesExtensions.find(
        (extension) => extension.id === ext.categorie_permis_extensible_id
      ) as any;
      if (cat) {
        return cat.id;
      }
      return;
    });

    if (typeof this.permis_prealable_id) {
      this.category.permis_prealable = this.permis_prealable_id as any;
    }
    if (this.category.id) {
      this.update();
    } else {
      this.post();
    }
  }

  post() {
    this.errorHandler.clearServerErrorsMessages('category-permis-basic');
    const categoryData = this.prepareData();
    categoryData.tranche_age_groupe = this.tranches;

    this.categoryPermisService
      .post(categoryData)
      .pipe(
        this.errorHandler.handleServerError(
          'category-permis-basic',
          (response) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        emitAlertEvent('Catégorie de permis ajoutée avec succès!', 'success');
        this.onLoading = false;
        redirectTo(undefined, 500);
      });
  }
  private update() {
    this.category.status = Boolean(this.category.status);
    this.updateCategory();
  }
  /**
   * Le jeu d'accordion au niveau des extensions
   * @param index
   * @param event
   */
  selectAccordion(index: number, event: Event) {
    event.preventDefault();
    //Toggle l'accordion
    if (this.accordionIndex === index) {
      if (this.accordionIndex == null) {
        this.accordionIndex = index;
      } else {
        this.accordionIndex = null;
      }
    } else {
      this.accordionIndex = index;
    }
  }

  hasExtensions(target: any) {
    if (target) {
      this.has_extensions = target.value == '1';
      this.basicFormHasChange = true;
    }
  }
  isExtension(target: any) {
    if (target) {
      this.is_extension = target.value == '1';
      this.basicFormHasChange = true;
      if (!this.is_extension) {
        this.category.montant_extension = undefined;
        delete this.category.montant_extension;
      }
    }
  }

  /**
   * Ajoute une catégorie d'extension au permis courant
   * @param target
   * @returns
   */
  addExtension(target: any) {
    if (this.catExtSelected == '') {
      emitAlertEvent('Veuillez sélectionner un permis');
      return;
    }
    const extensionToAdd = this.extensions.find(
      (ext) => ext.name === this.catExtSelected
    );
    if (!extensionToAdd) {
      //
      const dbPm = this.lesExtensions.find(
        (x) => x.name === this.catExtSelected
      );

      if (this.category.id) {
        this.categoryPermisService
          .addExtension({
            categorie_permis_id: this.category.id ?? 0,
            categorie_permis_extensible_id: (dbPm !== undefined
              ? dbPm?.id
              : 0) as any,
          })
          .pipe(this.errorHandler.handleServerError('category-permis-validite'))
          .subscribe((response) => {
            this.extensions.push({
              name: this.catExtSelected,
              categorie_permis_id: this.category.id,
              categorie_permis_extensible_id: dbPm?.id,
            });
            emitAlertEvent('Extension ajoutée avec succès', 'success');
          });
      } else {
        this.extensions.push({
          name: dbPm?.name as string,
          categorie_permis_id: this.category.id,
          categorie_permis_extensible_id: dbPm?.id,
        });
      }
    } else {
      emitAlertEvent('Cette extension est déjà liée au permis!');
    }
    this.catExtSelected = '';
    this.basicFormHasChange = true;
  }

  /**
   * Lance une alerte quand l'utilisateur clique sur le button de suppresion d'une extension
   * @param name
   */
  showExtRemovingAlert(name: string) {
    this.pm_ext_to_remove = name;
  }
  /**
   * Supprimer depuis le frontend ou depuis la base de données
   */
  deletePmExtension() {
    //Si le permis existe déjà
    let permisExt = this.extensions.find((pm) => {
      return pm.name == this.pm_ext_to_remove;
    });
    //
    if (permisExt) {
      let existInDatabase = false;
      if (Array.isArray(this.category.extensions)) {
        /**
         * On vérifie si la catégorie existe déjà
         */
        existInDatabase = permisExt.id !== undefined;
      }

      if (!existInDatabase) {
        //On supprime directement du front
        this.extensions = this.extensions.filter((pm) => pm !== permisExt);
        this.catExtSelected = '';
        this.pm_ext_to_remove = null;
      } else {
        this.errorHandler.startLoader("Retrait d'extension en cours!");
        //On supprime depuis la base de donnée
        this.categoryPermisService
          .removeCatExtension(permisExt.id ?? 0)
          .pipe(this.errorHandler.handleServerErrors())
          .subscribe((data) => {
            emitAlertEvent(data.message, 'success');
            this.pm_ext_to_remove = '';
            this.errorHandler.stopLoader();
            this.extensions = this.extensions.filter((pm) => pm !== permisExt);
          });
      }
    }
  }

  hasTranche(target: any) {
    if (target) {
      this.has_tranche = target.value === '1';
      this.forms.trncheAgeStateChanged = true;
    }

    if (this.has_tranche) {
      this.addNewTrancheAge = true;
    }
  }
  /**
   *  Ajoute une nouvelle tranche localement ou dans la base de données
   * @param event
   * @returns
   */
  addTranche(event: any) {
    this.cancelRemoveTranche();
    if (!this.tranches.includes(this.tranche)) {
      if (!this.validateTrancheAge()) {
        return;
      }
      this.onPostingTranches = true;
      if (this.category.id) {
        this.errorHandler.startLoader();
        this.errorHandler.startLoader('Ajout en cours ...');
        delete this.tranche.id;
        const tranche: any = this.tranche;
        tranche.categorie_permis_id = this.category.id;
        tranche.validite = this.tranche.validite?.toString(); //Ceci à cause d'une erreur que le server renvoie
        this.trancheAgeService
          .post(tranche)
          .pipe(
            this.errorHandler.handleServerError(
              'category-permis-validite',
              (response) => {
                this.onPostingTranches = false;
              }
            )
          )
          .subscribe((response) => {
            emitAlertEvent("Tranche d'âge ajoutée avec succès!", 'success');
            this.onPostingTranches = false;
            this.errorHandler.stopLoader();
            this.tranches.push(this.tranche);
            this.tranche = {} as TrancheAge;
          });
      } else {
        this.tranches.push(this.tranche);
        this.tranche = {} as TrancheAge;
        this.onPostingTranches = false;
      }
    }
  }

  appendNewTranche() {
    this.addNewTrancheAge = true;
    this.editTranche = false;
    this.tranche = {} as TrancheAge;
    this.cancelRemoveTranche();
  }

  openRemoveTrancheAlert(tranche: TrancheAge) {
    this.trancheToDelete = tranche;
  }

  cancelRemoveTranche() {
    this.trancheToDelete = {} as TrancheAge;
  }
  removeTranche() {
    const tranche = this.trancheToDelete;

    //Si la tranche d'âge existe dans la base de donnée
    if (tranche) {
      this.errorHandler.startLoader('Suppression en cours');
      this.trancheAgeService
        .delete(tranche.id ?? 0)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.tranches = this.tranches.filter(
            (tr) => tr !== this.trancheToDelete
          );
          this.errorHandler.stopLoader();
          this.cancelRemoveTranche();
          emitAlertEvent("Tranche d'âge supprimée avec succès!", 'success');
          this.category.trancheage = this.tranches;
        });
    }
  }

  private prepareData() {
    const categoryData = {
      ...this.category,
      tranche_age_groupe: [] as TrancheAge[],
    };
    if (!this.tranches.length) {
      this.tranches.push({
        validite: this.tranche.validite,
        age_max: null,
        age_min: null,
      });
    } else {
    }
    return categoryData;
  }

  /**
   * La première fois que la vue est prête
   */
  ngAfterViewInit(): void {
    $('#category-permis-basic').on('change', (e) => {
      this.basicFormHasChange = true;
    });

    $('#category-permis-extension').on('change', (e) => {
      this.basicFormHasChange = true;
    });

    this.setupExtensionValidation();

    this.validateValidity();
  }
  /**
   * Mettre les valeurs internes à jour
   */
  private setTrancheageFromServer() {
    this.tranche = {} as TrancheAge;
    if (this.category.id) {
      this.addNewTrancheAge = false;
      this.editPage = true;
      this.has_extensions = !!this.category.extensions;
      if (this.has_extensions) {
        //Ce map convertir les catégories
        this.extensions =
          (this.category.extensions?.map((x: any) => {
            const extFinded = this.lesExtensions.find(
              (ext) => ext.id === x.categorie_permis_extensible_id
            );
            if (extFinded) {
              x.name = extFinded.name;
              return x;
            }
            return x;
          }) as any) ?? [];
        this.extensions = this.extensions.filter((x) => !!x.name);
      }
      //Le button d'ajout de tranche d'âge sera éteint par défaut
      this.is_extension = Boolean(this.category.is_extension);
      const trancheages = this.category.trancheage as any;
      // Si le tableau dépasse 2 élément d'office ça dispose de tranche age
      this.has_tranche = trancheages.length > 1;
      this.syncTrancheAgeFromServerData();
    }
  }

  private updateCategory() {
    if (this.basicFormHasChange) {
      this.categoryPermisService
        .update(this.category, this.category.id ?? 0)
        .pipe(
          this.errorHandler.handleServerError(
            'category-permis-basic',
            (response) => {
              this.onLoading = false;
            }
          )
        )
        .subscribe((response) => {
          this.onLoading = false;
          emitAlertEvent(response.message, 'success');
        });
    } else {
      emitAlertEvent('Aucune donnée à modifier');
    }
  }

  cancelAddTranche() {
    this.addNewTrancheAge = false;
    this.editTranche = false;
    this.tranche = {} as TrancheAge;
  }

  /**
   * Si la tranche est modifié
   */
  updateTranche() {
    if (!this.validateTrancheAge()) {
      return;
    }
    if (this.tranche.id) {
      this.onPostingTranches = true;
      this.trancheAgeService
        .update(this.tranche, this.tranche.id)
        .pipe(
          this.errorHandler.handleServerError(
            'category-permis-validite',
            (response) => {
              this.onPostingTranches = false;
            }
          )
        )
        .subscribe((response) => {
          emitAlertEvent('Modification effectuée avec succès!', 'success');
          this.onPostingTranches = false;
        });
    }
  }

  /**
   *  Modifie une tranche d'âge
   * @param index
   */
  editTrancheAge(index: number) {
    this.tranche = this.tranches[index] ?? this.tranche;
    if (this.tranche) {
      this.editTranche = true;
      this.addNewTrancheAge = false;
    }
  }

  basicFormIsValid() {
    return this.forms.enableBasicOnEdit || this.forms.basicIsValid;
  }

  trancheFormIsValid() {
    return this.forms.enableTrancheOnEdit || this.forms.trancheIsValid;
  }
  private syncTrancheAgeFromServerData() {
    const trancheages = this.category.trancheage as any;
    if (this.has_tranche) {
      this.tranches = trancheages;
      const lastIndex = this.tranches.length - 1;
      //On prend la dernière
      this.tranche = this.tranches[lastIndex >= 0 ? lastIndex : 0];
    } else {
      const tranche = trancheages[0];

      if (tranche) {
        //Ceci conservera le tableau même si c'est un seul élément dans le tableau
        this.tranches = trancheages;
        //Si age min et max existent
        this.has_tranche = tranche.age_min !== null && tranche.age_max !== null;
        if (!this.has_tranche) {
          this.tranche = tranche;
          this.tranches = [];
        }
      }
      this.tranche = tranche;
    }
  }

  private resetData() {
    this.tranche = {} as TrancheAge;
    this.is_extension = false;
    this.has_tranche = false;
    this.has_extensions = false;
    this.basicFormHasChange = false;
    const firstWindowAction = $('#category-window')
      .find('[data-window-action]')
      .get(0);
    if (firstWindowAction) {
      firstWindowAction.click();
    }
  }

  private setupExtensionValidation(): any {
    $('#category-permis-validite').on('qv.form.validated', (e) => {
      this.extensionFormValid = true;
    });
    $('#category-permis-validite').on('qv.form.invalidated', (e) => {
      this.extensionFormValid = false;
    });
  }

  private validateTrancheAge() {
    if (!this.tranche.age_min || !this.tranche.age_max) {
      emitAlertEvent(
        "L'âge minimal et maximal sont requis",
        'warning',
        'middle',
        true
      );
      return;
    }
    if (this.tranche.age_min >= this.tranche.age_max) {
      emitAlertEvent(
        "L'âge minimal doît être strictement inférieur à l'âge maximal",
        'warning',
        'middle',
        true
      );
      return false;
    }
    //Si la tranche d'âge existe déjà
    if (
      this.tranches.some(
        (tr) =>
          tr.age_max == this.tranche.age_max &&
          tr.age_min == this.tranche.age_min
      )
    ) {
      if (!this.tranche.id) {
        emitAlertEvent(
          "Cette tranche d'âge existe pour cette catégorie déjà",
          'warning',
          'middle',
          true
        );
        return;
      }
    }

    return true;
  }

  private validateValidity() {
    //@ts-ignore
    this.basicQvForm = new QvForm('#category-permis-basic');
    this.basicQvForm.init();

    /**
     * Quand Quickv indique le formulaire est invalide
     */
    this.basicQvForm.onFails((e: any) => {
      this.forms.basicIsValid = false;
      this.forms.enableBasicOnEdit = false;
    });
    /**
     * Quand Quickv indique le formulaire est valide
     */
    this.basicQvForm.onPasses((e: any) => {
      this.forms.basicIsValid = true;
    });

    //@ts-ignore
    this.tranchQvForm = new QvForm('#category-permis-validite');
    this.tranchQvForm.init();
    /**
     * Ceci est appélé par ce que le formulaire de tranche  est appéle à changer dynamiqument
     */
    this.tranchQvForm.observeChanges();
    /**
     * Quand Quickv indique le formulaire est invalide
     */
    this.tranchQvForm.onFails((e: any) => {
      this.forms.trancheIsValid = false;
      this.forms.enableTrancheOnEdit = false;
    });
    /**
     * Quand Quickv indique le formulaire est valide
     */
    this.tranchQvForm.onPasses((e: any) => {
      this.forms.trancheIsValid = true;
    });
  }
  /**
   * Il faut manipuler ce hook avec soin, parce qu'il est appélé à chaque interraction avec la vue
   */
  ngAfterViewChecked(): void {
    if (this.forms.trncheAgeStateChanged) {
      //Indique à Qv de mettre à jour la validation sans ça même si la tranche change Qv ne fera rien
      this.tranchQvForm.emit('qv.form.updated');
    }
    this.forms.trncheAgeStateChanged = false;
  }
  /**
   * Ceci aussi est important pour détruire les instances de QvForm
   */
  ngOnDestroy(): void {
    this.basicQvForm.destroy();
    this.tranchQvForm.destroy();
  }
}
