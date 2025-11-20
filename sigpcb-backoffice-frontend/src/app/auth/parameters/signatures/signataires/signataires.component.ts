import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { Signataire } from 'src/app/core/interfaces/signataire';
import { User } from 'src/app/core/interfaces/user.interface';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SignataireService } from 'src/app/core/services/signataire.service';
import { UsersService } from 'src/app/core/services/users.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';

@Component({
  selector: 'app-signataires',
  templateUrl: './signataires.component.html',
  styleUrls: ['./signataires.component.scss'],
})
export class SignatairesComponent {
  signataires: any[] = [];
  users: User[] = [];

  titre_formulaire = 'Ajouter signataire';

  activateId: number | null = null;

  signataire = {} as Signataire;

  modalId = 'add-signataires';

  action: ModalActions = 'store';

  onLoading = false;

  constructor(
    private userService: UsersService,
    private signataireService: SignataireService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.signataire.user_id = '0' as any; //Sans ceci la sélection par défaut ne marhce pas

    this.getSignataires();
    this.getUsers();
  }
  refresh() {
    this.getSignataires();
  }
  private getSignataires() {
    this.errorHandler.startLoader();
    this.signataireService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.signataires = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private getUsers() {
    this.errorHandler.startLoader();
    this.userService
      .getUsersAll()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.users = response.data;
        this.errorHandler.stopLoader();
      });
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    this.postSignataire();
  }
  private postSignataire() {
    this.signataireService
      .post(this.signataire)
      .pipe(
        this.errorHandler.handleServerError(
          'signataire-form',
          (response: ServerResponseType) => {
            if (response.message) {
              this.onLoading = false;
            }
          }
        )
      )
      .subscribe((response) => {
        if (response.status) {
          this.hideModal();
          this.setAlert('Le signataire a été ajouté avec succès!', 'success');
          this.onLoading = false;
        }
        this.getSignataires();
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

  private hideModal() {
    $(`#${this.modalId}`).modal('hide');
  }

  destroy(id: number) {
    this.signataireService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.getSignataires();
        this.setAlert('Le signataire supprimé avec succès', 'success');
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.signataire = {} as User;
    if (action == 'edit') {
      this.titre_formulaire = 'Modification de signataire';
    } else if (action == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
    } else {
      this.titre_formulaire = 'Ajouter  un signataire';
    }
    if (object) {
      this.signataire = object;
    }
    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }
}
