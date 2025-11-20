import { Component, ElementRef } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Chapitre } from 'src/app/core/interfaces/chapitre';
import { BrowserEventServiceService } from 'src/app/core/services/browser-event-service.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ChapitreService } from 'src/app/core/services/chapitre.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-chapitres',
  templateUrl: './chapitres.component.html',
  styleUrls: ['./chapitres.component.scss'],
})
export class ChapitresComponent {
  public data: any = [];
  public selectedItems: any = [];
  public settings = {};
  public categories = [];
  chapitres: any[] = [];
  chapitre = {} as any;
  categorie: any;

  titre_formulaire = 'Ajouter un chapitre';
  activateId: number | null = null;
  modalId = 'add-chapitres';

  action: 'store' | 'edit' | 'show' | string = 'store';

  searchUrl = apiUrl('/chapitres');

  onLoading = false;

  chapitre_id: any;

  loadingForList: boolean = false;

  constructor(
    private chapitreService: ChapitreService,
    private categorieService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.getCategories();
    this.get();
    this.settings = {
      singleSelection: false,
      idField: 'id',
      textField: 'name',
      enableCheckAll: false,
      // selectAllText: 'Chọn All',
      // unSelectAllText: 'Hủy chọn',
      allowSearchFilter: false,
      limitSelection: -1,
      // maxHeight: 197,
      itemsShowLimit: 3,
      // searchPlaceholderText: 'Tìm kiếm',
      noDataAvailablePlaceholderText: 'non disponible',
      closeDropDownOnSelection: false,
      showSelectedItemsAtTop: false,
      defaultOpen: false,
    };
  }

  refresh() {
    this.get();
  }

  private get() {
    this.errorHandler.startLoader();
    this.loadingForList = true;
    this.chapitreService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.chapitres = response.data;
        }
        this.loadingForList = false;
        this.errorHandler.stopLoader();
      });
  }

  private getCategories() {
    this.categorieService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.categories = response.data;
        }
      });
  }

  showChapitre(id: any, action: any) {
    this.errorHandler.startLoader();
    this.chapitreService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.chapitre = response.data;
          if (action == 'show') {
            this.openModal('show', this.chapitre);
          } else {
            this.openModal('edit', this.chapitre);
          }
          this.errorHandler.stopLoader();
        }
      });
  }

  private post(categorie_permis_ids: any) {
    var data: any;
    data = this.chapitre;
    data.categorie_permis_ids = categorie_permis_ids;
    this.chapitreService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError(
          'chapitres-form',
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

  private update(categorie_permis_ids: any) {
    var data: any;
    data = this.chapitre;
    data.categorie_permis_ids = categorie_permis_ids;
    this.chapitreService
      .update(data, this.chapitre.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'chapitres-form',
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

  destroy(id: number) {
    this.errorHandler.startLoader('Suppresion en cours');
    this.chapitreService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Chapitre supprimé avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.selectedItems = [];
    this.chapitre = {} as Chapitre;
    if (action == 'edit') {
      this.titre_formulaire = 'Modification de chapitre';
    } else if (action == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
    } else {
      this.titre_formulaire = 'Ajouter un chapitre';
    }
    if (object) {
      this.chapitre = object;
      if (this.chapitre.categories_permis) {
        this.chapitre.categories_permis.map((chapitre_categorie: any) => {
          console.log(chapitre_categorie);
          // this.categories.find((categorie: any) => {
          //   if (categorie.id == chapitre_categorie.categorie_id) {
          this.selectedItems.push(chapitre_categorie);
          //   }
          // });
        });
      }
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
    var categorie_permis_ids: any[] = [];
    this.selectedItems.map((categorie: any) => {
      categorie_permis_ids.push(categorie.id);
    });
    if (categorie_permis_ids.length == 0) {
      this.setAlert(
        'Veuillez sectionner au moins une catgégorie',
        'warning',
        'middle',
        true
      );
      this.onLoading = false;
      return;
    }
    if (this.chapitre.id) {
      this.update(categorie_permis_ids);
    } else {
      this.post(categorie_permis_ids);
    }
  }

  onSearches(response: ServerResponseType) {
    if (response.status) {
      this.chapitres = response.data.data ?? response.data;
      //Si la réponse n'est pas bonne on reprend les anciennes données
      if (
        !is_array(this.chapitres) ||
        (is_array(this.chapitres) && this.chapitres.length < 1)
      ) {
        this.get();
      }
    } else {
      this.setAlert(response.message, 'danger', 'middle', true);
      this.get();
    }
  }
}
