import { Component, ElementRef } from '@angular/core';
import { ActeSigne } from 'src/app/core/interfaces/acte-signe';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { Signataire } from 'src/app/core/interfaces/signataire';
import { ActeSigneService } from 'src/app/core/services/acte-signe.service';
import { BrowserEventServiceService } from 'src/app/core/services/browser-event-service.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SignataireService } from 'src/app/core/services/signataire.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-acte-signes',
  templateUrl: './acte-signes.component.html',
  styleUrls: ['./acte-signes.component.scss'],
})
export class ActeSignesComponent {
  public data: any = [];
  public selectedItems_one_assign: any = [];
  public settings_multiple: any = {};
  public selectedItems_multiple_assign: any = [];
  public settings_one: any = {};
  public signataires: any = [];
  public signataires_one: any = [];

  actesignes: any[] = [];
  // signataires: Signataire[] = [];

  titre_formulaire = "Ajout d'un acte signé";

  alert = {
    message: "Une erreur inattendue s'est produite",
    type: 'danger' as AlertType,
    position: 'bottom-right' as AlertPosition,
    open: false,
  };
  actesigne = {} as ActeSigne;

  adminLines: number[] = [];

  modalId = 'add-actesignes';

  modalOneAssign = 'assign-one-signataire';
  modalMultipleAssign = 'assign-multiple-signataire';

  action: ModalActions = 'store';

  onLoading = false;

  constructor(
    private signataireService: SignataireService,
    private acteSigneService: ActeSigneService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getSignataires();
    this.settings_one = {
      singleSelection: true,
      idField: 'id',
      textField: 'user_name',
      enableCheckAll: false,
      allowSearchFilter: false,
      limitSelection: -1,
      // maxHeight: 197,
      itemsShowLimit: 3,
      noDataAvailablePlaceholderText: 'Non disponible',
      closeDropDownOnSelection: false,
      showSelectedItemsAtTop: false,
      defaultOpen: false,
    };

    this.settings_multiple = {
      singleSelection: false,
      idField: 'id',
      textField: 'user_name',
      enableCheckAll: false,
      allowSearchFilter: false,
      limitSelection: -1,
      // maxHeight: 197,
      itemsShowLimit: 3,
      noDataAvailablePlaceholderText: 'Non disponible',
      closeDropDownOnSelection: false,
      showSelectedItemsAtTop: false,
      defaultOpen: false,
    };
  }

  onIsOneSignataireChange() {}
  refresh() {
    this.get();
  }
  private get() {
    this.errorHandler.startLoader();
    this.acteSigneService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.actesignes = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private getSignataires() {
    this.errorHandler.startLoader();
    this.signataireService
      .get()
      .pipe(this.errorHandler.handleServerError('acte-sign-form'))
      .subscribe((response) => {
        if (response.status) {
          // On crée une liste de signataire à partir des utilisateurs
          this.signataires = [];
          response.data.map((signataire: any) => {
            signataire.user_name =
              signataire.user.first_name + ' ' + signataire.user.last_name;
            this.signataires.push(signataire);
          });
          this.errorHandler.stopLoader();
        }
      });
  }

  show(id: any, action: any) {
    this.actesigne = this.actesignes.find((actesigne) => actesigne.id == id);
    if (action == 'show') {
      this.openModal('show', this.actesigne);
    } else if (action == 'edit') {
      this.openModal('edit', this.actesigne);
    }
    this.acteSigneService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.actesigne = response.data;
      });
  }

  showAssign(id: any) {
    this.acteSigneService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.actesigne = response.data;
          this.openAssignModal(this.actesigne);
        }
      });
  }

  private post() {
    this.acteSigneService
      .post(this.actesigne)
      .pipe(
        this.errorHandler.handleServerError(
          'acte-sign-form',
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

  private update() {
    this.acteSigneService
      .update(this.actesigne, this.actesigne.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'acte-sign-form',
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

  saveAssign(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    var signataires_ids: any[] = [];
    //Si l'acte sera signé par un seul signataire
    if (this.actesigne.is_one_signataire) {
      this.selectedItems_one_assign.map((signataire: any) => {
        signataires_ids.push(signataire.id);
      });
    } else {
      this.selectedItems_multiple_assign.map((signataire: any) => {
        signataires_ids.push(signataire.id);
      });
    }
    //Appel au service pour ajouter l'assignement
    this.acteSigneService
      .assign({
        acte_signable_id: this.actesigne.id,
        is_one_signataire: this.actesigne.is_one_signataire,
        signataire_ids: signataires_ids,
      })
      .pipe(
        this.errorHandler.handleServerError(
          'acte-sign-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        if (response.data.is_one_signataire) {
          $(`#${this.modalOneAssign}`).modal('hide');
        } else {
          $(`#${this.modalMultipleAssign}`).modal('hide');
        }
        this.get();
      });
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (!this.actesigne.status) {
      this.actesigne.status = false;
    }
    if (!this.actesigne.is_one_signataire) {
      this.actesigne.is_one_signataire = false;
    }
    // Si l'acte signable existait on fait une miseà jour, sinon on crée un nouvel acte
    if (this.actesigne.id) {
      this.update();
    } else {
      this.post();
    }
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

  openModal(action: 'store' | 'edit' | 'show' | 'assign', object?: any) {
    this.actesigne = {} as ActeSigne;
    if (action == 'edit') {
      this.titre_formulaire = "Modification d'acte signé";
    } else if (action == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
    } else if (action == 'store') {
      this.titre_formulaire = 'Ajouter un acte signé';
    } else {
      this.titre_formulaire = 'Joindre signataire à ';
    }
    if (object) {
      this.actesigne = object;
    }
    this.action = action;
    this.signataires_one = [];
    this.signataires_one = this.signataires;
    $(`#${this.modalId}`).modal('show');
  }

  openAssignModal(object?: any) {
    this.actesigne = {} as ActeSigne;
    this.selectedItems_one_assign = [];
    this.selectedItems_multiple_assign = [];

    if (object) {
      this.actesigne = object;
    }
    // console.log(this.actesigne)
    this.titre_formulaire = 'Joindre signataire à ' + this.actesigne.name;
    if (this.actesigne.is_one_signataire) {
      if (this.actesigne.signataires) {
        this.actesigne.signataires.map((acte_signataire: any) => {
          this.signataires.find((signataire: any) => {
            if (signataire.id == acte_signataire.id) {
              signataire.user_name =
                signataire.user.first_name + ' ' + signataire.user.last_name;
              this.selectedItems_one_assign.push(signataire);
            }
          });
        });
      }
      $(`#${this.modalOneAssign}`).modal('show');
    } else {
      if (this.actesigne.signataires) {
        this.actesigne.signataires.map((acte_signataire: any) => {
          this.signataires.find((signataire: any) => {
            if (signataire.id == acte_signataire.id) {
              signataire.user_name =
                signataire.user.first_name + ' ' + signataire.user.last_name;
              this.selectedItems_multiple_assign.push(signataire);
            }
          });
        });
      }
      $(`#${this.modalMultipleAssign}`).modal('show');
    }
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.acteSigneService
      .status({ acte_signable_id: data.id, status: data.status })
      .pipe(
        this.errorHandler.handleServerError(
          'acte-sign-form',
          (response: ServerResponseType) => {
            if (response.message) {
              this.setAlert(
                response.message as string,
                'danger',
                'middle',
                true
              );
            }
          }
        )
      )
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activé' : 'désactivé';
          this.hideModal();
          this.setAlert(`L'acte a été ${content} avec succès !`, 'success');
          this.actesignes = this.actesignes.map((actesigne) => {
            if (actesigne.id == data.id) {
              actesigne.status = data.status;
            }
            return actesigne;
          });
        }
      });
  }

  destroy(id: number) {
    this.acteSigneService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('L`acte est supprimé avec succès', 'success');
      });
  }
}
