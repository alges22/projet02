import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { Titre } from 'src/app/core/interfaces/titre';
import { BrowserEventServiceService } from 'src/app/core/services/browser-event-service.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { TitreService } from 'src/app/core/services/titre.service';
@Component({
  selector: 'app-admin-titres',
  templateUrl: './admin-titres.component.html',
  styleUrls: ['./admin-titres.component.scss'],
})
export class AdminTitresComponent {
  titres: any[] = [];

  titre = {} as Titre;

  modalId = 'titre';

  titre_formulaire = "Ajout d'un titre";

  activateId: number | null = null;

  onLoading = false;

  action: ModalActions = 'store';

  constructor(
    private titreService: TitreService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
  }
  refresh() {
    this.titres = [];
    this.get();
  }
  post() {
    this.errorHandler.clearServerErrorsMessages('admin-titres-form');
    this.titreService
      .post(this.titre)
      .pipe(
        this.errorHandler.handleServerError('admin-titres-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.onLoading = false;
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.titreService
      .all()
      .pipe(this.errorHandler.handleServerError('admin-titres-form'))
      .subscribe((response) => {
        this.titres = response.data;
        this.errorHandler.stopLoader();
      });
  }

  /**
   * Ferme le modal
   */
  private hideModal() {
    $(`#${this.modalId}`).modal('hide');
  }

  private setAlert(
    message: string = '',
    type: AlertType = 'warning',
    position: AlertPosition = 'bottom-right',
    fixed?: boolean
  ) {
    this.errorHandler.emitAlert(message, type, position, fixed);
  }

  openModal(action: ModalActions, object?: any) {
    this.titre = {} as Titre;
    if (object) {
      this.titre = object;
    }
    if (action == 'edit') {
      this.titre_formulaire = 'Formulaire de modification';
    } else {
      this.titre_formulaire = 'Ajouter un titre';
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    this.titre.status = Boolean(this.titre.status);
    if (this.titre.id) {
      this.update();
    } else {
      this.post();
    }
  }
  private update() {
    this.titre.status = Boolean(this.titre.status);
    this.titreService
      .update(this.titre, this.titre.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('admin-titres-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.setAlert(response.message, 'success');
        this.onLoading = false;
        this.hideModal();
      });
  }
  edit(id: any) {
    this.titre = this.titres.find((titre) => titre.id == id);

    this.openModal('edit', this.titre);

    // Actualisation
    this.titreService
      .findById(id)
      .pipe(this.errorHandler.handleServerError('admin-titres-form'))
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.titre = response.data;
        }
      });
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.titreService
      .status({ titre_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('admin-titres-form'))
      .subscribe((response) => {
        const content = data.status ? 'activé' : 'désactivé';
        this.setAlert(`Le titre a été ${content} avec succès !`, 'success');
      });
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours ...');
    this.titreService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Titre supprimé avec succès', 'success');
      });
  }
}
