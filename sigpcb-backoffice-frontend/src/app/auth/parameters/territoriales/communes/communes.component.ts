import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Commune } from 'src/app/core/interfaces/commune';
import { Departement } from 'src/app/core/interfaces/departement';
import { CommuneService } from 'src/app/core/services/commune.service';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-communes',
  templateUrl: './communes.component.html',
  styleUrls: ['./communes.component.scss'],
})
export class CommunesComponent {
  communes: Commune[] = [];
  departements: Departement[] = [];
  commune = {} as Commune;

  titre_formulaire = "Formulaire d`'ajout";
  modalId = 'add-users';

  action: 'store' | 'edit' | 'show' | string = 'store';

  searchUrl = apiUrl('/communes');

  pageNumber = 1;

  paginate_data!: any;
  noResults = 'Aucune commnune';

  onLoading = false;
  constructor(
    private communeService: CommuneService,
    private departementService: DepartementService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getDepartements();
  }

  refresh() {
    this.get();
  }
  private get() {
    this.errorHandler.startLoader();
    this.communeService
      .getCommunes(this.pageNumber)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        if (response.status) {
          this.paginate_data = response.data;
          this.communes = this.paginate_data.data;
        }
      });
  }

  private getDepartements() {
    this.departementService
      .getDepartements()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.departements = response.data;
        }
      });
  }

  showCommune(id: any, action: any) {
    this.communeService
      .findById(id)
      .pipe(this.errorHandler.handleServerError('communes-form'))
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.commune = response.data;
          if (action == 'show') {
            this.openModal('show', this.commune);
          } else {
            this.openModal('edit', this.commune);
          }
        }
      });
  }

  private updateCommune() {
    this.onLoading = true;
    this.communeService
      .update(this.commune, this.commune.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('communes-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.get();
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.commune = {} as Commune;
    if (object) {
      this.commune = object;
    }
    if (action == 'edit') {
      this.titre_formulaire = `Modifier la commune <b>${this.commune.name}</b>`;
    } else if (action == 'show') {
      this.titre_formulaire = `La commune<b>: ${this.commune.name}</b>`;
    } else {
      this.titre_formulaire = 'Ajouter une commune';
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
    if (this.commune.id) {
      this.updateCommune();
    }
  }

  onSearches(response: any) {
    if (response.status) {
      this.communes = response.data.data ?? response.data;
      //Si la réponse n'est pas bonne on reprend les anciennes données
      if (
        !is_array(this.communes) ||
        (is_array(this.communes) && this.communes.length < 1)
      ) {
        this.noResults = 'Aucune commune trouvée';
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
