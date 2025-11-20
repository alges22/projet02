import { Component } from '@angular/core';
import { AlertType, AlertPosition } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { Restriction } from 'src/app/core/interfaces/restriction';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RestrictionService } from 'src/app/core/services/restriction.service';

@Component({
  selector: 'app-restriction',
  templateUrl: './restriction.component.html',
  styleUrls: ['./restriction.component.scss'],
})
export class RestrictionComponent {
  restrictions: any[] = [];

  restriction = {} as Restriction;

  modalId = 'restriction';

  restriction_formulaire = "Ajout d'une restriction";

  activateId: number | null = null;

  onLoading = false;

  action: ModalActions = 'store';

  constructor(
    private restrictionService: RestrictionService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
  }
  refresh() {
    this.restrictions = [];
    this.get();
  }
  post() {
    this.errorHandler.clearServerErrorsMessages('restriction-form');
    this.restrictionService
      .post(this.restriction)
      .pipe(
        this.errorHandler.handleServerError('restriction-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.hideModal();
        this.setAlert('Restriction ajoutée avec succès!', 'success');
        this.onLoading = false;
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.restrictionService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.restrictions = response.data;
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
    this.restriction = {} as Restriction;
    if (action == 'edit') {
      this.restriction_formulaire = 'Formulaire de modification';
    } else {
      this.restriction_formulaire = "Formulaire d'ajout d'une restriction";
    }
    if (object) {
      this.restriction = object;
    }
    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (this.restriction.id) {
      this.update();
    } else {
      this.post();
    }
  }
  private update() {
    this.restrictionService
      .update(this.restriction, this.restriction.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('restriction-form', (response) => {
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
    this.restriction = this.restrictions.find((ua) => ua.id == adminId);

    this.openModal('edit', this.restriction);

    // Actualisation
    this.restrictionService
      .findById(adminId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.restriction = response.data;
        }
      });
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.restrictionService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        this.get();
        this.setAlert('Restriction supprimée avec succès', 'success');
      });
  }
}
