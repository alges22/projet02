import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Inspecteur } from 'src/app/core/interfaces/inspecteur';
import { User } from 'src/app/core/interfaces/user.interface';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { InspecteurService } from 'src/app/core/services/inspecteur.service';
import { UsersService } from 'src/app/core/services/users.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';

@Component({
  selector: 'app-inspecteurs',
  templateUrl: './inspecteurs.component.html',
  styleUrls: ['./inspecteurs.component.scss'],
})
export class InspecteursComponent {
  pageIndex: number | null = 0;
  modalInspecteur = 'inspecteur';
  modalAssignInspecteur = 'assign-inspecteur';
  titre_formulaire = "Edition d'un superviseur de salle";
  titre_formulaire_assign = 'Assignation de salle aux inspecteurs';
  inspecteurAction: 'add' | 'edit' | string = 'add';
  inspecteurs: any[] = [];
  users: User[] = [];
  inspecteur = {} as Inspecteur;
  annexe: any;
  annexes: AnnexeAnatt[] = [];
  onLoading = false;
  salles: any[] = [];
  inspecteursannexe: any[] = [];
  inspecteursannexed: any[] = [];
  public settings_multiple: any = {};
  public selectedItems_multiple_assign: any = [];
  sessions: any[] = [];
  session = '';
  salle = '';
  showAssignation: boolean = true;
  displayInspecteurAnnexe = false;
  inspecteurIds: number[] = [];

  constructor(
    private userService: UsersService,
    private annexeService: AnnexeAnattService,
    private inspecteurService: InspecteurService,
    private examenService: ExamenService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit(): void {
    // this.signataire.user_id = '0' as any; //Sans ceci la sélection par défaut ne marhce pas

    this.getInspecteurs();
    this.getAnnexes();
    this.getUsers();
    this.getSessions();
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

  selectedPage(idpage: number) {
    if (idpage === 0) {
      this.pageIndex = 0;
    } else if (idpage === 1) {
      this.pageIndex = 1;
      // this.getAnnexeSalles(this.annexeanatt.id);
    } else if (idpage === 2) {
      this.pageIndex = 2;
      // this.getAnnexeSalles(this.annexeanatt.id);
    }
  }

  isSessionDisabled(session: any): boolean {
    const today = new Date();
    const dateGestionRejet = new Date(session.fin_gestion_rejet_at);

    dateGestionRejet.setDate(dateGestionRejet.getDate() - 1);

    // Si la date de gestion de rejet est dépassée par rapport à la date actuelle, on désactive la session
    return dateGestionRejet < today;
  }

  onSalleSelectionChange() {
    if (this.salle) {
      // Enable the session selection if a salle is selected
      this.session = ''; // Reset session selection
    }
  }

  onSessionSelectionChange() {
    if (this.session) {
      this.getInspecteursBySalleExamen({
        salle_compo_id: this.salle,
        examen_id: this.session,
      });
    }
  }

  appendInspecteur(event: Event) {
    const target = event.target as HTMLInputElement;
    const inspecteurId = parseInt(target.value, 10);
    if (target.checked) {
      // Ajouter inspecteurId s'il n'est pas déjà présent
      if (!this.inspecteurIds.includes(inspecteurId)) {
        this.inspecteurIds.push(inspecteurId);
      }
    } else {
      // Supprimer inspecteurId
      const index = this.inspecteurIds.indexOf(inspecteurId);
      if (index !== -1) {
        this.inspecteurIds.splice(index, 1);
      }
    }
    // this._allSelected();
  }

  inputChecked(id: number) {
    return this.inspecteurIds.includes(id);
  }

  private getInspecteursBySalleExamen(data: any) {
    this.errorHandler.startLoader();
    this.inspecteurService
      .getInspecteursBySalleExamen(data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.inspecteurIds = response.data.map((inspecteursalle: any) =>
            parseInt(inspecteursalle.inspecteur_id, 10)
          );
          // this.getInspecteursByAnnexeId(this.annexe.id);
          this.inspecteursannexe = this.inspecteursannexed;
          this.displayInspecteurAnnexe = true;
          this.errorHandler.stopLoader();
        }
      });
  }

  openInspecteurModal(
    inspecteurAction: 'store' | 'edit' | 'show',
    object?: any
  ) {
    this.inspecteurAction = inspecteurAction;
    this.inspecteur = {};
    // this.inspecteurs = [];
    if (object) {
      this.inspecteur = object;
    }
    $(`#${this.modalInspecteur}`).modal('show');
  }

  private getInspecteurs() {
    this.errorHandler.startLoader();
    this.inspecteurService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.inspecteurs = response.data;
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

  private getAnnexes() {
    this.errorHandler.startLoader();
    this.annexeService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexes = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private getSessions() {
    this.errorHandler.startLoader();
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.sessions = response.data;
        this.errorHandler.stopLoader();
      });
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    if (this.inspecteur.id) {
      this.updateInspecteur();
    } else {
      this.postInspecteur();
    }
  }
  private postInspecteur() {
    this.inspecteurService
      .post(this.inspecteur)
      .pipe(
        this.errorHandler.handleServerError(
          'inspecteur-form',
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
          this.setAlert(
            'Le superviseur de salle a été ajouté avec succès!',
            'success'
          );
          this.onLoading = false;
        }
        this.getInspecteurs();
      });
  }

  private updateInspecteur() {
    this.inspecteurService
      .update(this.inspecteur, this.inspecteur.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'inspecteur-form',
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
          this.setAlert(response.message, 'success');
          this.onLoading = false;
        }
        this.getInspecteurs();
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
    $(`#${this.modalInspecteur}`).modal('hide');
  }

  deleteInspecteur(id: number) {
    this.errorHandler.startLoader('Suppression en cours...');
    this.inspecteurService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.inspecteur = {};
        this.getInspecteurs();
        this.setAlert('Superviseur de salle supprimé avec succès', 'success');
      });
  }

  assign(annexe: any) {
    this.annexe = annexe;
    let id = this.annexe.id;
    this.displayInspecteurAnnexe = false;
    this.salle = '';
    this.session = '';
    this.inspecteurIds = [];
    this.errorHandler.startLoader();
    this.inspecteurService
      .getSalleByAnnexeId(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.salles = response.data;
          this.getInspecteursByAnnexeId(this.annexe.id);
          this.showAssignation = false;
          this.selectedPage(2);
          this.errorHandler.stopLoader();
        }
      });
  }

  private getInspecteursByAnnexeId(id: number) {
    this.errorHandler.startLoader();
    this.inspecteurService
      .getInspecteursByAnnexeId(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.inspecteursannexed = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }

  saveAssign(event: Event) {
    event.preventDefault();
    if (this.inspecteurIds.length > 0) {
      this.onLoading = true;
      this.inspecteurService
        .assign({
          examen_id: this.session,
          salle_compo_id: this.salle,
          inspecteur_ids: this.inspecteurIds,
        })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.onLoading = false;
          this.setAlert('Assignation effectuée avec succès', 'success');
          $(`#${this.modalAssignInspecteur}`).modal('hide');
          // this.salle = '';
          this.getAnnexes();
        });
    }
  }
}
