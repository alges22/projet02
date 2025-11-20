import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from './../../../../core/services/langue.service';
import { Component } from '@angular/core';
import { Langue } from 'src/app/core/interfaces/langue';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';

@Component({
  selector: 'app-param-langues',
  templateUrl: './param-langues.component.html',
  styleUrls: ['./param-langues.component.scss'],
})
export class ParamLanguesComponent {
  langues: any[] = [];

  langue = {} as Langue;

  modalId = 'langue';

  langue_formulaire = "Ajout d'une langue";

  activateId: number | null = null;

  onLoading = false;

  action: ModalActions = 'store';

  constructor(
    private langueService: LangueService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
  }
  refresh() {
    this.langues = [];
    this.get();
  }
  post() {
    this.errorHandler.clearServerErrorsMessages('langue-form');
    this.langueService
      .post(this.langue)
      .pipe(
        this.errorHandler.handleServerError('langue-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.hideModal();
        this.setAlert('Langue ajoutée avec succès!', 'success');
        this.onLoading = false;
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.langueService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.langues = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }

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
    this.langue = {} as Langue;
    if (action == 'edit') {
      this.langue_formulaire = 'Formulaire de modification';
    } else {
      this.langue_formulaire = "Formulaire d'ajout d'une langue";
    }
    if (object) {
      this.langue = object;
    }
    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (this.langue.id) {
      this.update();
    } else {
      this.post();
    }
  }
  private update() {
    this.langue.status = Boolean(this.langue.status);
    this.langueService
      .update(this.langue, this.langue.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('langue-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
      });
  }
  edit(adminId: any) {
    this.langue = this.langues.find((ua) => ua.id == adminId);

    this.openModal('edit', this.langue);

    // Actualisation
    this.langueService
      .findById(adminId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.langue = response.data;
        }
      });
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.langueService
      .status({ langue_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activée' : 'désactivée';
          this.setAlert(`La langue a été ${content} avec succès !`, 'success');
        }
      });
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.langueService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        this.get();
        this.setAlert('Langue supprimée avec succès', 'success');
      });
  }
}
