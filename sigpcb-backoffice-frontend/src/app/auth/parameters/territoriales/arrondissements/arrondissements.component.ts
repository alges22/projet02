import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Arrondissement } from 'src/app/core/interfaces/arrondissement';
import { Commune } from 'src/app/core/interfaces/commune';
import { ArrondissementService } from 'src/app/core/services/arrondissement.service';
import { CommuneService } from 'src/app/core/services/commune.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-arrondissements',
  templateUrl: './arrondissements.component.html',
  styleUrls: ['./arrondissements.component.scss'],
})
export class ArrondissementsComponent {
  arrondissements: Arrondissement[] = [];
  communes: Commune[] = [];
  arrondissement = {} as Arrondissement;

  adminLines: number[] = [];

  titre_formulaire = 'Ajouter un arrondissement';
  modalId = 'add-users';

  action: 'store' | 'edit' | 'show' | string = 'store';

  searchUrl = apiUrl('/arrondissements');
  pageNumber = 1;

  paginate_data!: any;

  noResults = 'Aucun arrondissement';

  onLoading = false;
  constructor(
    private arrondissementService: ArrondissementService,
    private communeService: CommuneService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getCommunes();
  }

  refresh() {
    this.get();
  }

  private get() {
    this.errorHandler.startLoader();
    this.arrondissementService
      .getArrondissements(this.pageNumber)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.arrondissements = this.paginate_data.data;
        this.errorHandler.stopLoader();
      });
  }

  private getCommunes() {
    this.errorHandler.startLoader();

    this.communeService
      .getCommunes(-1, 'all')
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.communes = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  showArrondissement(id: any, action: any) {
    this.arrondissementService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.arrondissement = response.data;
          if (action == 'show') {
            this.openModal('show', this.arrondissement);
          } else {
            this.openModal('edit', this.arrondissement);
          }
        }
      });
  }

  private postArrondissement() {
    this.onLoading = true;
    this.arrondissementService
      .post(this.arrondissement)
      .pipe(
        this.errorHandler.handleServerError(
          'arrondissement-form',
          (response) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.get();
      });
  }

  private updateArrondissement() {
    this.arrondissementService
      .update(this.arrondissement, this.arrondissement.id ?? 0)
      .pipe(this.errorHandler.handleServerError('arrondissement-form'))
      .subscribe((response) => {
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.get();
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.arrondissement = {} as Arrondissement;
    if (action == 'edit') {
      this.titre_formulaire = 'Formulaire de modification';
    } else if (action == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
    } else {
      this.titre_formulaire = 'Ajouter un arrondissement';
    }
    if (object) {
      this.arrondissement = object;
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
    if (this.arrondissement.id) {
      this.updateArrondissement();
    } else {
      this.postArrondissement();
    }
  }

  onSearches(response: any) {
    if (response.status) {
      this.arrondissements = response.data.data ?? response.data;
      //Si la réponse n'est pas bonne on reprend les anciennes données
      if (
        !is_array(this.arrondissements) ||
        (is_array(this.arrondissements) && this.arrondissements.length < 1)
      ) {
        this.noResults = 'Aucun arrondissement trouvé';
      }
    } else {
      this.setAlert(response.message, 'danger', 'middle', true);
      this.get();
    }

    if (response.refresh) {
      this.get();
    }
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }
}
