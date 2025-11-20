import { Component, ElementRef } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { Departement } from 'src/app/core/interfaces/departement';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-departements',
  templateUrl: './departements.component.html',
  styleUrls: ['./departements.component.scss'],
})
export class DepartementsComponent {
  departements: Departement[] = [];
  departement = {} as Departement;

  titre_formulaire = "Formulaire d`'ajout";
  modalId = 'add-users';

  action: 'store' | 'edit' | 'show' | string = 'store';

  onLoading = false;
  constructor(
    private departementService: DepartementService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.getDepartements();
  }

  refresh() {
    this.getDepartements();
  }

  private getDepartements() {
    this.errorHandler.startLoader();
    this.departementService
      .getDepartements()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.departements = response.data;
        this.errorHandler.stopLoader();
      });
  }

  showDepartement(id: any, action: any) {
    this.departementService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.departement = response.data;
          if (action == 'show') {
            this.openModal('show', this.departement);
          } else {
            this.openModal('edit', this.departement);
          }
        }
      });
  }

  private updateDepartement() {
    this.onLoading = true;
    this.departementService
      .update(this.departement, this.departement.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('departement-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.setAlert(response.message, 'success');
        this.hideModal();
        this.getDepartements();
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.departement = {} as Departement;
    if (object) {
      this.departement = object;
    }
    if (action == 'edit') {
      this.titre_formulaire = `Modifier le département <b>${this.departement.name}</b>`;
    } else if (action == 'show') {
      this.titre_formulaire = `Le département : <b>${this.departement.name}</b>`;
    } else {
      this.titre_formulaire = 'Ajouter un département';
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

  private hideModal() {
    $(`#${this.modalId}`).modal('hide');
  }

  save(event: Event) {
    event.preventDefault();
    if (this.departement.id) {
      this.updateDepartement();
    } else {
      // this.postDepartement();
    }
  }
}
