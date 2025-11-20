import { ModalActions } from './../../../../core/interfaces/modal-actions';
import { UniteAdmin } from './../../../../core/interfaces/unite-admin';
import { Component, ElementRef, OnInit } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { UniteAdminService } from 'src/app/core/services/unite-admin.service';
import { apiUrl, is_array } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-admin-unite',
  templateUrl: './admin-unite.component.html',
  styleUrls: ['./admin-unite.component.scss'],
})
export class AdminUniteComponent implements OnInit {
  unite_admins: any[] = [];

  uadmin = {} as UniteAdmin;

  modalId = 'unite-admin';

  titre_formulaire = "Ajout d'une unité administrative";

  activateId: number | null = null;

  onLoading = false;

  action: ModalActions = 'store';

  searchUrl = apiUrl('/unite-admins');

  results: any[] = [];

  unite_admins_filtered: any[] = [];

  constructor(
    private uadminService: UniteAdminService,
    private errorHandler: HttpErrorHandlerService,
    private refElement: ElementRef<HTMLElement>
  ) {}

  ngOnInit(): void {
    this.get();
  }
  refresh() {
    this.unite_admins = [];
    this.get();
  }
  post() {
    this.uadmin.status = Boolean(this.uadmin.status);
    this.uadminService
      .post(this.uadmin)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoading = false;
        }, 'unite-admins-form')
      )
      .subscribe((response) => {
        this.get();
        this.hideModal();
        this.setAlert('Unité administrative ajoutée avec succès!', 'success');
        this.onLoading = false;
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.uadminService
      .all()
      .pipe(this.errorHandler.handleServerError('unite-admins-form'))
      .subscribe((response) => {
        this.unite_admins = response.data;
        this.unite_admins.map((ua) => {
          if (ua.ua_parent_id !== null) {
            ua.parent = this.unite_admins.find(
              (uniteAdmin) => uniteAdmin.id == ua.ua_parent_id
            );
          }
        });

        this.errorHandler.stopLoader();
      });
  }

  /**
   * Clique le button de fermeture de modal
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
    this.errorHandler.clearServerErrorsMessages('agregateur-form');
    this.uadmin = {} as UniteAdmin;
    if (action == 'edit') {
      this.titre_formulaire = "Ajout d'une unité administrative";
    } else {
      this.titre_formulaire = 'Ajouter une unité administrative';

      //Filtrer l'unité administrative
      this.unite_admins_filtered = this.unite_admins.filter((ua) => ua.status);
    }
    if (object) {
      this.uadmin = object;
    }
    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (this.uadmin.id) {
      this.update();
    } else {
      this.post();
    }
  }
  private update() {
    this.uadmin.status = Boolean(this.uadmin.status);
    this.uadminService
      .update(this.uadmin, this.uadmin.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('unite-admins-form', (response) => {
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
  edit(adminId: any) {
    this.uadmin = this.unite_admins.find((ua) => ua.id == adminId);
    //Filtrer l'unité administrative
    this.unite_admins_filtered = this.unite_admins.filter(
      (ua) => ua.id !== adminId && ua.status
    );
    this.openModal('edit', this.uadmin);

    // Actualisation
    this.uadminService
      .findById(adminId)
      .pipe(this.errorHandler.handleServerError('unite-admins-form'))
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.uadmin = response.data;
        }
      });
  }

  toggleStatus(data: { id: number; status: boolean }) {
    this.uadminService
      .status({ unite_admin_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('unite-admins-form'))
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activée' : 'désactivée';
          this.setAlert(
            `L'unité administrative a été ${content} avec succès !`,
            'success'
          );
          this.unite_admins = this.unite_admins.map((admin) => {
            if (admin.id == data.id) {
              admin.status = data.status;
            }
            return admin;
          });
        }
      });
  }
  onSearches(response: any) {
    if (response.status) {
      this.unite_admins = response.data.data ?? response.data;
      if (!is_array(this.unite_admins)) this.unite_admins = [];
    } else {
      this.setAlert(response.message, 'danger', 'middle', true);
    }
    if (response.refresh) {
      this.get();
    }
  }

  destroy(uaminId: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.uadminService
      .delete(uaminId)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Unité administrative supprimée avec succès', 'success');
      });
  }
}
