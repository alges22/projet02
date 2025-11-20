import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { Role } from 'src/app/core/interfaces/role';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PermissionService } from 'src/app/core/services/permission.service';
import { RoleService } from 'src/app/core/services/role.service';

@Component({
  selector: 'app-role-list',
  templateUrl: './role-list.component.html',
  styleUrls: ['./role-list.component.scss'],
})
export class RoleListComponent {
  roles: any = [];

  permissions: any;

  role = {} as any;

  modalId = 'role';

  role_formulaire = "Ajout d'un role";

  activateId: number | null = null;

  onLoading = false;

  action: ModalActions = 'store';

  constructor(
    private roleService: RoleService,
    private permissionService: PermissionService,
    private errorHandler: HttpErrorHandlerService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.get();
    this.getPermissions();
  }
  refresh() {
    this.roles = [];
    this.get();
  }
  post() {
    this.errorHandler.clearServerErrorsMessages('admin-roles-form');
    this.roleService
      .post(this.role)
      .pipe(
        this.errorHandler.handleServerError('admin-roles-form', (response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.get();
        this.hideModal();
        this.onLoading = false;
      });
  }

  get() {
    this.errorHandler.startLoader();
    this.roleService
      .get()
      .pipe(this.errorHandler.handleServerError('admin-roles-form'))
      .subscribe((response) => {
        this.roles = response.data;
        this.errorHandler.stopLoader();
      });
  }

  addRole() {
    this.router.navigate(['/parametres/gestions/roles/add']);
  }

  getPermissions() {
    this.errorHandler.startLoader();
    this.permissionService
      .get()
      .pipe(this.errorHandler.handleServerError('admin-roles-form'))
      .subscribe((response) => {
        const myArray = Object.keys(response.data).map((key) => ({
          onglet: key,
          permissions: response.data[key],
        }));
        this.permissions = myArray;
        this.errorHandler.stopLoader();
      });
  }

  isPermided(permission_id: any) {
    if (this.role && this.role.permissions) {
      return this.role.permissions.some((id: any) => id === permission_id);
    }
    return false;
  }

  updateRolePermission(event: Event, permissionId: number) {
    if (!this.role.permissions) {
      this.role.permissions = []; // initialize the array if it is undefined
    }
    const isChecked = (event.target as HTMLInputElement).checked;
    const index = this.role.permissions.indexOf(permissionId);

    if (isChecked && index === -1) {
      this.role.permissions.push(permissionId);
    } else if (!isChecked && index !== -1) {
      this.role.permissions.splice(index, 1);
    }
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
    this.role = {} as Role;
    if (object) {
      this.role = object;
    }
    if (action == 'edit') {
      this.role_formulaire = 'Edition de rôle';
    } else {
      this.role_formulaire = 'Ajouter un rôle';
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (this.role.id) {
      this.update();
    } else {
      this.post();
    }
  }
  private update() {
    this.roleService
      .update(this.role, this.role.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError('admin-roles-form', (response) => {
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
  private rolePermissionsId(role: any) {
    role.permissions = role.permissions.map((permission: any) => permission.id);
    return role;
  }
  editRole(id: any) {
    this.router.navigate(['/parametres/gestions/roles/edit', id]);
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.roleService
      .status({ role_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('admin-roles-form'))
      .subscribe((response) => {
        const content = data.status ? 'activé' : 'désactivé';
        this.setAlert(`Le role a été ${content} avec succès !`, 'success');
      });
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours ...');
    this.roleService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.setAlert('Role supprimé avec succès', 'success');
      });
  }
}
