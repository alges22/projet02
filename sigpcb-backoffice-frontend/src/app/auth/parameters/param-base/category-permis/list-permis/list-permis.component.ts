import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { TrancheAge } from 'src/app/core/interfaces/tranche-age';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { TrancheAgeService } from 'src/app/core/services/tranche-age.service';

@Component({
  selector: 'app-list-permis',
  templateUrl: './list-permis.component.html',
  styleUrls: ['./list-permis.component.scss'],
})
export class ListPermisComponent {
  categories: any[] = [];

  category = {} as CategoryPermis;

  modalId = 'category-modal';

  category_formulaire = "Ajout d'une catégorie de permis";

  activateId: number | null = null;

  onDeleting = false;

  onLoading = false;

  action: ModalActions = 'store';

  /**
   * Association
   */
  category_tranche = {} as CategoryPermis;

  tranches: TrancheAge[] = [];
  // Liste de tableau des associations
  cpts: {
    category: CategoryPermis;
    tranche: TrancheAge;
    validite: number;
  }[] = [];

  cp_tranche = { category_permis_id: 1, tranche_id: 0, validite: 1 };

  //Association catégorie tranche
  categories_tranches: any[] = [];

  constructor(
    private categoryPermisService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService,
    private trancheAgeService: TrancheAgeService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getTrancheAges();
    this.getcategoriesTranches();
  }
  /**
   * Reactualise la liste des utilisateurs
   */
  refresh() {
    this.get();
  }

  get() {
    this.errorHandler.startLoader();
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  destroy(category_id: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.categoryPermisService
      .delete(category_id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert(
          'La catégorie de permis a été supprimée avec succès',
          'success'
        );
      });
  }

  getTrancheAges() {
    this.trancheAgeService.all().subscribe((response) => {
      const tranches = response.data;
      if (Array.isArray(tranches)) {
        //Les tranches d'ages actives
        this.tranches = tranches.filter((t) => t.status);
      }
    });
  }

  /**
   * Ajoute une ligne de tranche age
   */
  addTrancheAges() {
    this.cp_tranche.category_permis_id = this.category_tranche.id as any;

    const category = this.categories.find(
      (c) => c.id == this.cp_tranche.category_permis_id
    );

    const tranche = this.tranches.find(
      (t) => t.id == (this.cp_tranche.tranche_id as any)
    ) as any;
    const validite = this.cp_tranche.validite;

    if (category && tranche && validite) {
      const cpt = {
        category: category,
        tranche: tranche,
        validite: validite,
      };

      // Vérifier si l'objet similaire existe déjà dans le tableau
      const doublon = this.cpts.some(
        (c) => c.category.id === category.id && c.tranche.id === tranche.id
      );

      if (!doublon) {
        this.cpts.push(cpt);
      } else {
        this.cpts = this.cpts.map((cpt) => {
          if (tranche.id == cpt.tranche.id) {
            cpt.validite = validite;
          }
          return cpt;
        });
      }
    }

    if (!tranche) {
      this.setAlert(
        "Aucune tranche d'âge ajoutée, veuillez en ajouter une!",
        'warning'
      );
    }
  }
  /**
   *  Retire une ligne d'association catégorie-tranche age
   * @param index
   */
  removeCpt(index: number) {
    this.cpts.splice(index, 1);
  }
  /**
   * Assigne les tranches d'age à une catégorie
   * @param event
   */
  assignTranches(event: Event, id: any) {
    this.onLoading = true;
    event.preventDefault();
    const serverData = {
      categorie_permis_id: this.cp_tranche.category_permis_id,
      tranche_age_validites: [] as any,
    };
    for (const cpt of this.cpts) {
      serverData.tranche_age_validites.push({
        tranche_age_id: cpt.tranche.id as any,
        validite: cpt.validite,
      });
    }

    this.categoryPermisService
      .assignTrancheAge(serverData)
      .pipe(
        this.errorHandler.handleServerError(
          'category-permis-tranche' + id,
          (response) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoading = false;
        $(`#category-permis-${id}`).modal('hide');
        this.errorHandler.emitSuccessAlert(response.message);
      });
  }
  /**
   * Prend l'association catégorie/tranche
   */
  getcategoriesTranches() {
    this.categoryPermisService
      .getTrancheAges()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories_tranches = response.data;
      });
  }
}
