import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { Commune } from 'src/app/core/interfaces/commune';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CommuneService } from 'src/app/core/services/commune.service';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SalleCompoService } from 'src/app/core/services/salle-compo.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';
import { emitAlertEvent, is_array } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-annexe-form',
  templateUrl: './annexe-form.component.html',
  styleUrls: ['./annexe-form.component.scss'],
})
export class AnnexeFormComponent {
  questionAdded: any;

  constructor(
    private errorHandler: HttpErrorHandlerService,
    private annexeanattService: AnnexeAnattService,
    private sallecompoService: SalleCompoService,
    private departementService: DepartementService,
    private communeService: CommuneService,
    private route: ActivatedRoute
  ) {}

  annexeanatt_title = '';

  pageIndex: number | null = 0;

  assetLink = environment.endpoints.asset;
  annexe_formulaire: any;

  public data: any = [];
  public selectedItems: any = [];
  public settings = {};
  public departements = [];

  departementsEmplacements: any[] = [];
  annexeanatts: any[] = [];
  annexeanatt = {} as any;
  departement: any;
  commune: any;
  communesdepart = [] as Commune[];
  communes = [] as Commune[];
  loadingForList: boolean = false;
  sallecompos: any[] = [];
  salles: any[] = [];
  sallecompo = {} as any;

  titre_formulaire = "Formulaire d`'ajout";
  activateId: number | null = null;
  modalId = 'add-annexes-anatt';
  modalAssignSalle = 'assign-salle';

  action: 'store' | 'edit' | 'show' | string = 'store';

  salleAction: 'add' | 'edit' | string = 'add';

  onLoading = false;

  onLoadingSalle = false;

  ngOnInit(): void {
    this.getDepartements();
    // this.selectedItems = [];
    this.route.params.subscribe((params) => {
      const id = params['id'];
      this.annexe_formulaire = "Ajout d'une annexe";
      if (id) {
        this.getCommunesPromise().then(() => {
          return this.getAnnexeById(id);
        });

        this.annexe_formulaire = 'Edition de : ';
      } else {
        this.getCommunes();
      }
    });

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
      this.getAnnexeSalles(this.annexeanatt.id);
    }
  }

  private getAnnexeById(id: any) {
    this.selectedItems = [];
    var items: any = [];
    this.errorHandler.startLoader();
    this.annexeanattService
      .findById(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.data && response.data.id) {
          this.annexeanatt = response.data;
          this.communes = this.communesdepart.filter(
            (item) => item.departement_id == this.annexeanatt.departement_id
          );
          if (this.annexeanatt.annexe_anatt_departements) {
            this.annexeanatt.annexe_anatt_departements.map(
              (annexe_anatt_departement: any) => {
                this.departements.find((departement: any) => {
                  if (
                    departement.id == annexe_anatt_departement.departement_id
                  ) {
                    items.push(departement);
                  }
                });
              }
            );
            this.selectedItems = items;
          }
          this.annexeanatt_title = response.data.name;
          this.errorHandler.stopLoader();
        }
      });
  }

  private getCommunesPromise() {
    return new Promise((resolve, reject) => {
      this.errorHandler.startLoader();
      this.loadingForList = true;
      this.communeService
        .getCommunes(-1, 'all')
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.loadingForList = false;
            this.errorHandler.stopLoader();
            reject(response);
          })
        )
        .subscribe((response) => {
          this.loadingForList = false;
          if (response.status) {
            this.communesdepart = response.data;
            resolve(response);
          } else {
            reject(response);
          }
          this.errorHandler.stopLoader();
        });
    });
  }

  refresh() {
    this.getAnnexeAnatts();
  }

  private getAnnexeAnatts() {
    this.errorHandler.startLoader();
    this.loadingForList = true;
    this.annexeanattService
      .get()
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.loadingForList = false;
        })
      )
      .subscribe((response) => {
        if (response.status) {
          this.annexeanatts = response.data;
        }
        this.loadingForList = false;
        this.errorHandler.stopLoader();
      });
  }

  private getDepartements() {
    this.errorHandler.startLoader();
    this.departementService
      .getDepartements()
      .pipe(this.errorHandler.handleServerError('annexes-form'))
      .subscribe((response) => {
        if (response.status) {
          this.departements = response.data;
          this.departementsEmplacements = response.data;
        }
        this.errorHandler.stopLoader();
      });
  }

  private getCommunes() {
    this.errorHandler.startLoader();
    this.loadingForList = true;
    this.communeService
      .getCommunes(-1, 'all')
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.loadingForList = false;
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        this.loadingForList = false;
        if (response.status) {
          this.communesdepart = response.data;
        }
      });
  }

  getAnnexeSalles(id: any) {
    this.errorHandler.startLoader();
    this.annexeanattService
      .getSalleById(id)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        if (response.data) {
          this.salles = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }

  selectDep() {
    this.communes = this.communesdepart.filter(
      (item) => item.departement_id == this.annexeanatt.departement_id
    );
    this.commune = '';
  }

  findAnnexeCommuneInCommunes(element: any): any {
    return this.communesdepart.find((item) => item.id === element.commune_id);
  }

  private post(departement_ids: any) {
    var data: any;
    data = this.annexeanatt;
    data.departement_ids = departement_ids;
    this.annexeanattService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError(
          'annexes-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        emitAlertEvent('Annexe ajoutée avec succès!', 'success');
        this.onLoading = false;
        this.annexeanatt = response.data;
        $('#reset').click();
        this.getAnnexeById(this.annexeanatt.id);
      });
  }

  private update(departement_ids: any) {
    var data: any;
    data = this.annexeanatt;
    data.departement_ids = departement_ids;
    this.annexeanattService
      .update(data, this.annexeanatt.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'annexes-form',
          (response: ServerResponseType) => {
            this.onLoading = false;
          }
        )
      )
      .subscribe((response) => {
        emitAlertEvent('Annexe modifiée avec succès!', 'success');
        this.onLoading = false;
        this.annexeanatt = response.data;
        $('#reset').click();
        this.getAnnexeById(this.annexeanatt.id);
      });
  }

  deleteAnnexeAnatt(data: { id: number; status: boolean }) {
    this.annexeanattService
      .status({ annexe_anatt_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('annexes-form'))
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activé' : 'désactivé';
          this.hideModal();
          this.setAlert(`L'annexe a été ${content} avec succès !`, 'success');
          this.annexeanatts = this.annexeanatts.map((annexeanatt) => {
            if (annexeanatt.id == data.id) {
              annexeanatt.status = data.status;
            }
            return annexeanatt;
          });
        }
      });
  }

  confirmSwitch(data: { id: number; status: boolean }) {
    this.annexeanattService
      .status({ annexe_anatt_id: data.id, status: data.status })
      .pipe(this.errorHandler.handleServerError('annexes-form'))
      .subscribe((response) => {
        if (response.status) {
          const content = data.status ? 'activé' : 'désactivé';
          this.hideModal();
          this.setAlert(`L'annexe a été ${content} avec succès !`, 'success');
          this.annexeanatts = this.annexeanatts.map((annexeanatt) => {
            if (annexeanatt.id == data.id) {
              annexeanatt.status = data.status;
            }
            return annexeanatt;
          });
        }
      });
  }

  openSalleModal(salleAction: 'store' | 'edit' | 'show', object?: any) {
    console.log(salleAction);
    this.salleAction = salleAction;
    this.sallecompo = {};
    this.sallecompos = [];
    if (salleAction == 'edit') {
      this.titre_formulaire =
        'Edition de la salle de composition ' +
        object.name +
        " à l'annexe de " +
        this.annexeanatt.name;
    } else if (salleAction == 'show') {
      this.titre_formulaire = "Formulaire d'affichage";
    } else {
      this.titre_formulaire =
        "Ajout de salles de composition à l'annexe de " + this.annexeanatt.name;
    }
    if (object) {
      this.sallecompo = object;
    }
    $(`#${this.modalAssignSalle}`).modal('show');
  }

  openAssignModal(object?: any) {
    $(`#${this.modalAssignSalle}`).modal('show');
  }

  addAssignSalle(event: Event) {
    event.preventDefault();
    if (!this.sallecompo.name) {
      this.setAlert('Le nom est requis.', 'danger', 'middle');
      return;
    }
    if (
      this.sallecompos.some(
        (item) => item.name.toLowerCase() === this.sallecompo.name.toLowerCase()
      )
    ) {
      this.setAlert('Le nom doit être unique.', 'danger', 'middle');
      return;
    }

    this.sallecompos.push(this.sallecompo);
    this.sallecompo = {};
  }

  saveAssignSalle(event: Event) {
    event.preventDefault();
    if (this.sallecompos.length == 0) {
      this.setAlert(
        'Veuillez ajouter de salle de composition.',
        'danger',
        'middle'
      );
      return;
    }
    this.onLoading = true;
    this.annexeanattService
      .postSalleCompo({
        annexe_anatt_id: this.annexeanatt.id,
        salle_comp_groupes: this.sallecompos,
      })
      .pipe(
        this.errorHandler.handleServerError('annexes-form', (response: any) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        this.onLoading = false;
        this.setAlert(response.message, 'success');
        $(`#${this.modalAssignSalle}`).modal('hide');
        this.getAnnexeSalles(this.annexeanatt.id);
      });
  }

  deleteSalle(sallecompo: any) {
    const index = this.sallecompos.indexOf(sallecompo);
    if (index >= 0) {
      this.sallecompos.splice(index, 1);
    }
  }

  saveSalle(event: Event) {
    event.preventDefault();
    var data = {
      annexe_anatt_id: this.annexeanatt.id,
      name: this.sallecompo.name,
      contenance: this.sallecompo.contenance,
    };
    this.onLoadingSalle = true;
    if (this.sallecompo.id) {
      this.updateSalle(data);
    } else {
      this.postSalle(data);
    }
  }
  // pour enregistrer une salle pour une annexe
  private postSalle(data: any) {
    this.sallecompoService
      .post(data)
      .pipe(
        this.errorHandler.handleServerError(
          'salle-edit-form',
          (response: ServerResponseType) => {
            this.onLoadingSalle = false;
          }
        )
      )
      .subscribe((response) => {
        this.onLoadingSalle = false;
        this.setAlert(response.message, 'success');
        this.sallecompo = {};
        $(`#${this.modalAssignSalle}`).modal('hide');
        this.getAnnexeSalles(this.annexeanatt.id);
      });
  }

  private updateSalle(data: any) {
    this.sallecompoService
      .update(data, this.sallecompo.id ?? 0)
      .pipe(
        this.errorHandler.handleServerError(
          'salle-edit-form',
          (response: ServerResponseType) => {
            this.onLoadingSalle = false;
            this.setAlert(response.message, 'danger', 'middle');
          }
        )
      )
      .subscribe((response) => {
        this.onLoadingSalle = false;
        this.setAlert(response.message, 'success');
        this.sallecompo = {};
        $(`#${this.modalAssignSalle}`).modal('hide');
        this.getAnnexeSalles(this.annexeanatt.id);
      });
  }

  // pour supprimer cette salle de l'annexe
  deleteSalleAnnexe(id: number) {
    this.errorHandler.startLoader('Suppression en cours...');
    this.sallecompoService
      .delete(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.sallecompo = {};
        this.getAnnexeSalles(this.annexeanatt.id);
        this.setAlert('Salle supprimée avec succès', 'success');
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
    this.selectedItems = [];
    $(`#${this.modalId}`).modal('hide');
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoading = true;
    var departement_ids: any[] = [];
    this.selectedItems.map((departement: any) => {
      departement_ids.push(departement.id);
    });
    if (!this.annexeanatt.status) {
      this.annexeanatt.status = false;
    }
    if (this.annexeanatt.id) {
      this.update(departement_ids);
    } else {
      this.post(departement_ids);
    }
  }
}
