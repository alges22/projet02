import { is_array } from 'src/app/helpers/helpers';
import { apiUrl } from './../../../../helpers/helpers';
import { ModalActions } from './../../../../core/interfaces/modal-actions';
import { AlertPosition, AlertType } from './../../../../core/interfaces/alert';
import { User } from 'src/app/core/interfaces/user.interface';
import { TitreService } from './../../../../core/services/titre.service';
import { Titre } from './../../../../core/interfaces/titre';
import { UniteAdmin } from './../../../../core/interfaces/unite-admin';
import { Component, OnInit, ElementRef } from '@angular/core';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { UniteAdminService } from 'src/app/core/services/unite-admin.service';
import { UsersService } from 'src/app/core/services/users.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { RoleService } from 'src/app/core/services/role.service';

@Component({
  selector: 'app-admin-home',
  templateUrl: './admin-home.component.html',
  styleUrls: ['./admin-home.component.scss'],
})
export class AdminHomeComponent implements OnInit {
  admins: any[] = [];

  uadmins: UniteAdmin[] = [];

  titres: Titre[] = [];

  roles: any[] = [];

  titre_formulaire = "Ajout d'un administrateur";

  activateId: number | null = null;

  admin = {} as User;

  adminLines: number[] = [];

  modalId = 'add-users';

  action: ModalActions = 'store';

  searchUrl = apiUrl('/users');

  results: any[] = [];

  pageNumber = 1;

  paginate_data!: any;

  isPostingAdmin = false;

  noUsers = 'Aucun utilisateur';
  npiValid = false;
  npiInfo: any = null;
  constructor(
    private usersService: UsersService,
    private uadminService: UniteAdminService,
    private errorHandler: HttpErrorHandlerService,
    private refElement: ElementRef<HTMLElement>,
    private titreService: TitreService,
    private roleService: RoleService
  ) {}

  ngOnInit(): void {
    this.admin.titre_id = '0' as any; //Sans ceci la sélection par défaut ne marhce pas
    this.admin.unite_admin_id = '0' as any; //Sans ceci la sélection par défaut ne marhce pas

    this.get();
    this.getUnitesAdmins();
    this.getTitres();
    this.getRoles();
  }
  /**
   * Actualise la liste des administrateurs
   */
  refresh() {
    this.get();
  }
  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }
  private get() {
    this.errorHandler.startLoader();
    this.usersService
      .getAdmins(this.pageNumber)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.npiInfo = null;
        this.npiValid = false;
        if (response.status) {
          this.admins = response.data.data;
          this.paginate_data = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  private getUnitesAdmins() {
    this.uadminService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.uadmins = response.data.filter((u: any) => u.status);
        }
      });
  }

  private getTitres() {
    this.titreService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.titres = response.data.filter((t: any) => t.status);
        }
      });
  }

  private getRoles() {
    this.roleService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.roles = response.data;
        }
      });
  }

  save(event: Event) {
    event.preventDefault();
    this.isPostingAdmin = true;
    if (this.admin.id) {
      this.updateAdmin();
    } else {
      this.postAdmin();
    }
  }
  private postAdmin() {
    this.admin.status = Boolean(this.admin.status);
    this.usersService
      .postAdmin(this.admin)
      .pipe(
        this.errorHandler.handleServerErrors((response: ServerResponseType) => {
          if (response.message) {
            this.isPostingAdmin = false;
          }
        }, 'admin-form')
      )
      .subscribe((response) => {
        if (response.status) {
          this.hideModal();
          this.setAlert(
            "L'administrateur a été ajouté avec succès!",
            'success'
          );
          this.isPostingAdmin = false;
        }
        this.get();
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

  /**
   * Clique le button de fermeture de modal
   */
  private hideModal() {
    const modalButton =
      this.refElement.nativeElement?.querySelector<HTMLElement>(
        `[data-bs-dismiss]`
      );
    if (modalButton) {
      modalButton.click();
    }
  }
  // pour recuperer le premier role de l'utilisateur
  //ceci est fait car actuellement on a defini dans le html que
  //l'utilisateur n'a qu4un seul or au backend on a laisse sur plusieurs
  //donc au cas ou ils voudront changer on ne travaillera que sur le front
  public getOneRoleUser(admin: any) {
    var role: any;
    if (admin.roles) {
      if (admin.roles.length > 0) {
        role = admin.roles[0];
      }
    }
    return role;
  }

  editAdmin(id: any) {
    this.admin = this.admins.find((admin) => admin.id == id);
    if (this.getOneRoleUser(this.admin))
      this.admin.role_id = this.getOneRoleUser(this.admin).id;
    this.npiValid = !!this.admin.npi;
    this.openModal('edit', this.admin);
    //On fait une réactualisation
    this.usersService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.admin = response.data;
          if (this.getOneRoleUser(this.admin))
            this.admin.role_id = this.getOneRoleUser(this.admin).id;
          // if(admin.roles && admin.roles.length>0){
          //   this.admin.role_id = admin.roles[0].id
          // }
        }
      });
  }

  private updateAdmin() {
    this.admin.status = Boolean(this.admin.status);
    this.usersService
      .update(this.admin, this.admin.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'admin-form',
          (response: ServerResponseType) => {
            this.isPostingAdmin = false;
          }
        )
      )
      .subscribe((response) => {
        this.get();
        this.isPostingAdmin = false;
        this.setAlert(response.message, 'success');
        this.hideModal();
      });
  }

  show(id: number) {
    this.admin = this.admins.find((admin) => admin.id == id);
    this.openModal('show', this.admin);
    //On fait une réactualisation
    this.usersService
      .findById(id)
      .pipe(this.errorHandler.handleServerError('admin-form'))
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.admin = response.data;
        }
      });
  }

  openModal(action: 'store' | 'edit' | 'show', object?: any) {
    this.admin = {} as User;
    if (object) {
      this.admin = object;
    }
    this.errorHandler.clearServerErrorsMessages('admin-form');
    if (action == 'edit') {
      this.titre_formulaire = `Modification: ${this.admin.first_name} ${this.admin.last_name}`;
    } else if (action == 'show') {
      this.titre_formulaire = `Administrateur: ${this.admin.first_name} ${this.admin.last_name}`;
    } else {
      this.titre_formulaire = 'Ajouter un agent';
    }

    this.action = action;
    $(`#${this.modalId}`).modal('show');
  }

  toggleStatus(data: { id: number; status: boolean }) {
    this.usersService
      .status({ user_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('admin-form'))
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activé' : 'désactivé';
          this.hideModal();
          this.setAlert(
            `L'administrateur a été ${content} avec succès !`,
            'success'
          );
          this.admins = this.admins.map((admin) => {
            if (admin.id == data.id) {
              admin.sttus = data.status;
            }
            return admin;
          });
        }
      });
  }

  onSearches(response: any) {
    if (response.status) {
      this.admins = response.data.data ?? response.data;
      //Si la réponse est présente
      if (
        !is_array(this.admins) ||
        (is_array(this.admins) && this.admins.length < 1)
      ) {
        this.noUsers = 'Aucun utilisateur trouvé';
      }
    } else {
      this.setAlert(response.message, 'danger', 'middle', true);
    }

    if (response.refresh) {
      this.get();
    }
  }
  //Supprimer un utilisateur
  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours');
    this.usersService
      .delete(id)
      .pipe(this.errorHandler.handleServerError('admin-form'))
      .subscribe((response) => {
        this.get();
        this.setAlert('Administrateur supprimé avec succès', 'success');
        this.errorHandler.stopLoader();
      });
  }

  onNpi() {
    this.npiValid = false;
    const npi = this.admin.npi;
    if (npi.length > 9) {
      this.errorHandler.startLoader();
      this.usersService
        .npiInfos(npi)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.npiValid = true;
          this.npiInfo = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }
}
