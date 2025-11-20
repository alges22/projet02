import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Reponse } from 'src/app/core/interfaces/reponse';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ReponseService } from 'src/app/core/services/reponse.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-reponses',
  templateUrl: './reponses.component.html',
  styleUrls: ['./reponses.component.scss'],
})
export class ReponsesComponent {
  reponses: any[] = [];
  reponse = {} as Reponse;

  titre_formulaire = 'Ajouter une réponse';
  modalId = 'add-reponses';

  action: 'store' | 'edit' | 'show' | string = 'store';

  searchUrl = apiUrl('/reponses');

  onLoading = false;

  constructor(
    private reponseService: ReponseService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  refresh() {
    this.get();
  }

  private get() {
    this.errorHandler.startLoader();
    this.reponseService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.reponses = response.data;
        this.errorHandler.stopLoader();
      });
  }

  show(id: any, action: any) {
    this.reponse = this.reponses.find((reponse) => reponse.id == id);
    if (action == 'show') {
      this.openModal('show', this.reponse);
    } else {
      this.openModal('edit', this.reponse);
    }
    this.errorHandler.startLoader();
    this.reponseService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.reponse = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  private update() {
    this.reponseService
      .update(this.reponse, this.reponse.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'responses-form',
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

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.reponse = {} as Reponse;
    this.reponse.couleur = '#00FF00';
    if (action == 'edit') {
      this.titre_formulaire = 'Modification de Réponse';
    } else if (action == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
    } else {
      this.titre_formulaire = 'Ajouter une Réponse';
    }
    if (object) {
      this.reponse = object;
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
    if (this.reponse.id) {
      this.update();
    } else {
      this.post();
    }
  }

  private post() {
    this.reponseService
      .post(this.reponse)
      .pipe(
        this.errorHandler.handleServerError(
          'responses-form',
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
    this.errorHandler.startLoader();
    this.reponseService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Réponse supprimée avec succès', 'success');
      });
  }

  onColorChange() {
    console.log('Selected color:', this.reponse.couleur);
    // Autres actions à effectuer avec la couleur sélectionnée
  }
}
