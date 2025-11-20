import { Component, ElementRef } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { BaremeConduite } from 'src/app/core/interfaces/bareme-conduite';
import { BaremeConduiteService } from 'src/app/core/services/bareme-conduite.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-bareme-conduites',
  templateUrl: './bareme-conduites.component.html',
  styleUrls: ['./bareme-conduites.component.scss'],
})
export class BaremeConduitesComponent {
  baremeconduites: any[] = [];
  baremebycategories: any[] = [];
  baremeconduite = {} as any;
  baremebycategorie = {} as any;

  titre_formulaire = 'Ajouter un barème';
  modalId = 'add-baremeconduites';

  action: 'store' | 'edit' | 'show' | string = 'store';

  searchUrl = apiUrl('/bareme-conduites');

  onLoading = false;

  onDeleting = false;

  categories: any[] = [];

  categorie_permis_id: any;

  onLoadingBareme = false;

  categoryBareme: any = null;

  selectedBaremeId: number = 0;

  constructor(
    private readonly baremeconduiteService: BaremeConduiteService,
    private readonly categoryPermisService: CategoryPermisService,
    private readonly errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getCategories();
  }

  refresh() {
    this.get();
  }

  private get() {
    this.baremebycategories = [];
    this.errorHandler.startLoader();
    this.baremeconduiteService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.baremebycategories = response.data;
          //Par défaut on prend le premier élement
          this.setBaremeCategory(0);
        }
        this.errorHandler.stopLoader();
      });
  }

  findbaremeCategorieInCategories(element: any): any {
    return this.categories.find((item) => item.id === element.id);
  }

  getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.categories = response.data;
        }
      });
  }

  show(id: any, action: any) {
    this.baremebycategorie = this.baremebycategories.find(
      (baremebycategorie) => baremebycategorie.id == id
    );
    if (action == 'show') {
      this.openModal('show', this.baremebycategorie);
    } else {
      this.openModal('edit', this.baremebycategorie);
    }
    this.getBaremesByCategorieId(id);
  }

  private getBaremesByCategorieId(id: any) {
    this.baremeconduites = [];
    this.errorHandler.startLoader();
    this.baremeconduiteService
      .findBaremesByCategorieId(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          // console.log(response.data)
          this.baremebycategorie = response.data;
          if (response.data.baremes) {
            response.data.baremes.map((bareme: any) => {
              var baremeconduite = {
                id: bareme.id,
                name: bareme.name,
                poids: bareme.poids,
              };
              this.baremeconduites.push(baremeconduite);
            });
            this.errorHandler.stopLoader();
          }
        }
      });
  }

  showBareme(data: any) {
    this.baremeconduite = data;
    $('#name').focus();
  }

  saveBareme(event: Event) {
    event.preventDefault();
    const data = {
      categorie_permis_id: this.categoryBareme.id,
      name: this.baremeconduite.name,
      poids: this.baremeconduite.poids,
    };
    this.onLoadingBareme = true;
    if (this.baremeconduite.id) {
      this.updateBareme(data);
    } else {
      this.postBareme(data);
    }
    // this.get();
  }
  // pour enregistrer une reponse d'une question
  private postBareme(data: any) {
    this.baremeconduiteService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError(
          'bareme-conduite-form',
          (response: ServerResponseType) => {
            this.onLoadingBareme = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoadingBareme = false;
        this.setAlert(response.message, 'success');
        this.baremeconduite = {};
        this.getBaremesByCategorieId(this.baremebycategorie.id);
        this.get();
      });
  }

  private updateBareme(data: any) {
    this.baremeconduiteService
      .update(data, this.baremeconduite.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'bareme-conduite-form',
          (response: ServerResponseType) => {
            this.onLoadingBareme = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoadingBareme = false;
        this.setAlert(response.message, 'success');
        this.baremeconduite = {};
        this.getBaremesByCategorieId(this.baremebycategorie.id);
        this.get();
      });
  }

  // pour supprimer ce bareme de la categorie
  deleteBaremeCategorie(id: number) {
    this.onDeleting = true;
    this.errorHandler.startLoader('Suppression en cours ...');
    this.baremeconduiteService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.onDeleting = false;
        this.baremeconduite = {};
        this.getBaremesByCategorieId(this.baremebycategorie.id);
        this.setAlert('Barème supprimé avec succès', 'success');
        this.get();
      });
  }

  private update() {
    this.baremeconduiteService
      .update(this.baremeconduite, this.baremeconduite.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'bareme-conduite-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.get();
      });
  }

  addBareme(event: Event) {
    event.preventDefault();
    if (
      this.baremeconduites.some(
        (item) => item.name === this.baremeconduite.name
      )
    ) {
      this.setAlert('Le libellé doit être unique.', 'danger', 'middle');
      return;
    }
    if (!this.baremeconduite.name) {
      this.setAlert('Le libellé est requis.', 'danger', 'middle');
      return;
    }
    this.baremeconduites.push(this.baremeconduite);
    this.baremeconduite = {};
  }

  deleteBareme(baremeconduite: any) {
    const index = this.baremeconduites.indexOf(baremeconduite);
    if (index >= 0) {
      this.baremeconduites.splice(index, 1);
    }
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.baremeconduite = {} as BaremeConduite;
    if (action == 'edit') {
      this.titre_formulaire = 'Modification de Réponse';
    } else if (action == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
      if (object) {
        this.titre_formulaire =
          'Catégorie ' + object.name + ' : Gestion de barèmes';
      }
    } else {
      this.titre_formulaire = 'Ajout de barème';
      this.categorie_permis_id = {};
      this.baremeconduites = [];
    }
    if (object) {
      this.baremebycategorie = object;
    }
    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  /**
   * Clique le button de fermeture de modal
   */
  private hideModal() {
    $(`#${this.modalId}`).modal('hide');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (!this.categorie_permis_id) {
      this.setAlert(
        'Veuillez sélectionner la catégorie',
        'danger',
        'middle',
        true
      );
      this.onLoading = false;
      return;
    }

    if (this.baremeconduites.length == 0) {
      this.setAlert(
        'Veuillez ajouter au moins un barème',
        'danger',
        'middle',
        true
      );
      this.onLoading = false;
      return;
    }

    var data: any;
    data = {
      categorie_permis_id: this.categorie_permis_id,
      baremes: this.baremeconduites,
    };
    this.post(data);
    //
    // if (this.baremeconduite.id) {
    //   this.update();
    // } else {
    //   this.post();
    // }
  }

  private post(data: any) {
    this.baremeconduiteService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError(
          'bareme-conduite-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.get();
      });
  }

  onSearches(response: ServerResponseType) {
    if (response.status) {
      this.baremeconduites = response.data.data ?? response.data;
      //Si la réponse n'est pas bonne on reprend les anciennes données
      if (
        !is_array(this.baremeconduites) ||
        (is_array(this.baremeconduites) && this.baremeconduites.length < 1)
      ) {
        this.get();
      }
    } else {
      this.setAlert(response.message, 'danger', 'middle', true);
      this.get();
    }
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours ...');
    this.baremeconduiteService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Réponse supprimée avec succès', 'success');
      });
  }

  setBaremeCategory(index: number) {
    this.categoryBareme = this.baremebycategories[index] ?? null;
  }

  openBareme(bareme: any) {
    if (this.selectedBaremeId == bareme.id) {
      this.selectedBaremeId = 0;
    } else {
      this.selectedBaremeId = bareme.id;
    }
  }
}
