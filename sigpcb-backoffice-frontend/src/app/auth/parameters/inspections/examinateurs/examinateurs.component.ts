import { Component } from '@angular/core';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Examinateur } from 'src/app/core/interfaces/examinateur';
import { User } from 'src/app/core/interfaces/user.interface';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { ExaminateurService } from 'src/app/core/services/examinateur.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { UsersService } from 'src/app/core/services/users.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-examinateurs',
  templateUrl: './examinateurs.component.html',
  styleUrls: ['./examinateurs.component.scss'],
})
export class ExaminateursComponent {
  pageIndex: number | null = 0;
  modalExaminateur = 'examinateur';
  modalJury = 'jury';
  modalAssignExaminateur = 'assign-examinateur';
  titre_formulaire = "Edition d'un examinateur";
  titre_formulaire_jury = "Edition d'un jury";
  titre_formulaire_assign = 'Assignation de salle aux examinateurs';
  examinateurAction: 'add' | 'edit' | string = 'add';
  examinateurs: any[] = [];
  users: User[] = [];
  examinateur = {} as any;
  annexe: any;
  annexes: AnnexeAnatt[] = [];
  onLoading = false;
  salles: any[] = [];
  examinateursannexe: any[] = [];
  examinateursannexed: any[] = [];
  sessions: any[] = [];
  session = '';
  salle = '';
  onLoadingImport = false;
  public data: any = [];
  public selectedItems: any = [];
  public settings = {};
  public categories = [];
  // assign: any;
  displayExaminateurAnnexe = false;
  examinateurIds: number[] = [];
  jury = {} as any;
  juries: any[] = [];
  showJury: boolean = true;
  modalImportId = 'import';
  importFile: any;
  importFileName = 'import-model-examinateur.xlsx';

  constructor(
    private userService: UsersService,
    private annexeService: AnnexeAnattService,
    private examinateurService: ExaminateurService,
    private examenService: ExamenService,
    private errorHandler: HttpErrorHandlerService,
    private categoryPermisService: CategoryPermisService
  ) {}

  ngOnInit(): void {
    // this.signataire.user_id = '0' as any; //Sans ceci la sélection par défaut ne marhce pas

    this.getExaminateurs();
    this.getAnnexes();
    this.getUsers();
    this.getSessions();
    this.getCategories();
    this.settings = {
      singleSelection: false,
      idField: 'id',
      textField: 'name',
      enableCheckAll: false,
      allowSearchFilter: false,
      limitSelection: -1,
      itemsShowLimit: 3,
      noDataAvailablePlaceholderText: 'non disponible',
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
      this.getExaminateursBySalleExamen({
        salle_compo_id: this.salle,
        examen_id: this.session,
      });
    }
  }

  appendExaminateur(event: Event) {
    const target = event.target as HTMLInputElement;
    const examinateurId = parseInt(target.value, 10);
    if (target.checked) {
      // Ajouter examinateurId s'il n'est pas déjà présent
      if (!this.examinateurIds.includes(examinateurId)) {
        this.examinateurIds.push(examinateurId);
      }
    } else {
      // Supprimer examinateurId
      const index = this.examinateurIds.indexOf(examinateurId);
      if (index !== -1) {
        this.examinateurIds.splice(index, 1);
      }
    }
    // this._allSelected();
  }

  inputChecked(id: number) {
    return this.examinateurIds.includes(id);
  }

  private getExaminateursBySalleExamen(data: any) {
    this.errorHandler.startLoader();
    this.examinateurService
      .getExaminateursBySalleExamen(data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.examinateurIds = response.data.map((examinateursalle: any) =>
            parseInt(examinateursalle.examinateur_id, 10)
          );
          // this.getExaminateursByAnnexeId(this.annexe.id);
          this.examinateursannexe = this.examinateursannexed;
          this.displayExaminateurAnnexe = true;
          this.errorHandler.stopLoader();
        }
      });
  }

  private getExaminateurs() {
    this.errorHandler.startLoader();
    this.examinateurService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examinateurs = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private getCategories() {
    this.errorHandler.startLoader();
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
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

  openExaminateurModal(
    examinateurAction: 'store' | 'edit' | 'show',
    object?: any
  ) {
    this.examinateurAction = examinateurAction;
    this.examinateur = {};
    // this.examinateurs = [];
    this.selectedItems = [];
    if (object) {
      this.selectedItems = [];
      var items: any = [];
      this.examinateur = object;
      if (this.examinateur.examinateur_categorie_permis) {
        this.examinateur.examinateur_categorie_permis.map(
          (categorie_permis: any) => {
            this.categories.find((category: any) => {
              if (category.id == categorie_permis.categorie_permis_id) {
                items.push(category);
              }
            });
          }
        );
        this.selectedItems = items;
      }
    }
    $(`#${this.modalExaminateur}`).modal('show');
  }

  fetchCategories(examinateur_categories: any): string {
    const categoryNames: string[] = [];

    examinateur_categories.forEach((examinateur_categorie: any) => {
      const matchingCategory = this.categories.find(
        (category: any) =>
          category.id == examinateur_categorie.categorie_permis_id
      );
      if (matchingCategory) {
        // @ts-ignore
        categoryNames.push(matchingCategory.name);
      }
    });

    // Utilisez la méthode join pour fusionner les noms des catégories avec des espaces entre elles.
    return categoryNames.join(', ');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    var categorie_permis_ids: any[] = [];
    this.selectedItems.map((category: any) => {
      categorie_permis_ids.push(category.id);
    });
    if (this.examinateur.id) {
      this.updateExaminateur(categorie_permis_ids);
    } else {
      this.postExaminateur(categorie_permis_ids);
    }
  }
  private postExaminateur(categorie_permis_ids: any) {
    var data: any;
    data = this.examinateur;
    data.categorie_permis_ids = categorie_permis_ids;
    this.examinateurService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError(
          'examinateur-form',
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
          this.setAlert("L'examinateur a été ajouté avec succès!", 'success');
          this.onLoading = false;
        }
        this.getExaminateurs();
      });
  }

  private updateExaminateur(categorie_permis_ids: any) {
    var data: any;
    data = this.examinateur;
    data.categorie_permis_ids = categorie_permis_ids;
    this.examinateurService
      .update(data, this.examinateur.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'examinateur-form',
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
        this.getExaminateurs();
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
    $(`#${this.modalExaminateur}`).modal('hide');
  }

  deleteExaminateur(id: number) {
    console.log(id);
    this.errorHandler.startLoader('Suppression en cours...');
    this.examinateurService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examinateur = {};
        this.getExaminateurs();
        this.setAlert('Examinateur supprimé avec succès', 'success');
      });
  }

  assign(annexe: any) {
    this.annexe = annexe;
    let id = this.annexe.id;
    this.errorHandler.startLoader();
    this.examinateurService
      .getJuriesByAnnexeId(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.juries = response.data;
          this.showJury = false;
          this.selectedPage(2);
          this.errorHandler.stopLoader();
        }
      });
  }

  openJuryModal(juryAction: 'store' | 'edit' | 'show', object?: any) {
    this.jury = {};
    // this.inspecteurs = [];
    if (object) {
      this.jury = object;
    }
    $(`#${this.modalJury}`).modal('show');
  }

  saveJury(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    this.jury.annexe_anatt_id = this.annexe.id;
    if (this.jury.id) {
      this.updateJury();
    } else {
      this.postJury();
    }
  }

  private postJury() {
    this.examinateurService
      .postJury(this.jury)
      .pipe(
        this.errorHandler.handleServerError(
          'juries-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        $(`#${this.modalJury}`).modal('hide');
        this.getJuriesByAnnexeId(this.annexe.id);
      });
  }

  private updateJury() {
    this.examinateurService
      .updateJury(this.jury, this.jury.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'juries-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        $(`#${this.modalJury}`).modal('hide');
        this.getJuriesByAnnexeId(this.annexe.id);
      });
  }

  private getJuriesByAnnexeId(annexe_id: any) {
    this.errorHandler.startLoader();
    this.examinateurService
      .getJuriesByAnnexeId(annexe_id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.juries = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }

  deleteJury(id: number) {
    this.errorHandler.startLoader('Suppression en cours...');
    this.examinateurService
      .deleteJury(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.jury = {};
        this.getJuriesByAnnexeId(this.annexe.id);
        this.setAlert('Jury supprimé avec succès', 'success');
      });
  }

  importModal() {
    $(`#${this.modalImportId}`).modal('show');
  }

  onFileSelected(event: any) {
    if (event.target.files && event.target.files.length) {
      const file = event.target.files[0];
      this.importFile = file;
    }
  }

  import(event: Event) {
    event.preventDefault();
    const formData = new FormData();
    if (this.importFile) {
      formData.append('importfile', this.importFile);
    } else {
      emitAlertEvent(
        `Veuillez sélectionner le fichier à importer !!!`,
        'danger',
        'middle'
      );
      return;
    }

    this.onLoadingImport = true;
    this.postImportFile(formData);
  }

  private postImportFile(data: any) {
    this.examinateurService
      .postImportFile(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadingImport = false;
        })
      )
      .subscribe((response) => {
        emitAlertEvent('Importation effectuée avec succès!', 'success');
        this.onLoadingImport = false;
        $('#resetImportFile').click();
        $(`#${this.modalImportId}`).modal('hide');
        this.getExaminateurs();
      });
  }
}
