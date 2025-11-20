import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Dossier } from 'src/app/core/interfaces/candidat';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Commune } from 'src/app/core/interfaces/commune';
import { Departement } from 'src/app/core/interfaces/departement';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
type State = 'validate' | 'rejected' | 'failed' | 'refresh';
@Component({
  selector: 'app-auto-ecole-fiche',
  templateUrl: './auto-ecole-fiche.component.html',
  styleUrls: ['./auto-ecole-fiche.component.scss'],
})
export class AutoEcoleFicheComponent {
  @Output('validate') validateEvent = new EventEmitter<{
    autoecoleId: number;
    state: State;
    // autoecole: any;
  }>();
  @Input('data') data: any = null;
  @Input('communesdepart') communesdepart: any = null;
  @Input('departements') departements: any = null;
  previewUrl = '';
  modalEditId = 'edit';
  modalEditMPId = 'editMP';
  modalEditPromoteurId = 'editPromoteur';
  modalEditVehiculeId = 'editVehicule';
  auto_ecole_formulaire = "Edition d'une auto-école";
  mp_formulaire = "Edition d'un moniteur";
  promoteur_formulaire = "Edition d'un promoteur";
  vehicule_formulaire = "Edition d'un vehicule";
  moniteur: any;
  promoteur: any;
  subject = '';
  // object pour moniteur ou promoteur
  mp: any;
  vehicule: any;
  immatriculation = '';
  npi = '';
  // departements = [] as Departement;
  departementsEmplacements: any[] = [];
  // communesdepart = [] as Commune[];
  communes = [] as Commune[];
  onLoadingAutoSave = false;
  onLoadingMPSave = false;
  onLoadingVehiculeSave = false;
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private aecoleService: AecoleService
  ) {}

  //autoecole
  autoecole: any = null;

  @Input() page = 'pending' as 'validate' | 'rejected' | 'pending';
  vehicules: any[] = [];
  onValidate() {
    this.validateEvent.emit({
      autoecoleId: this.data.id,
      state: 'validate',
      // autoecole: this.autoecole,
    });
  }

  onRefresh() {
    this.validateEvent.emit({
      autoecoleId: this.data.id,
      state: 'refresh',
    });
  }

  onRejected() {
    this.validateEvent.emit({
      autoecoleId: this.data.id,
      state: 'rejected',
      // autoecole: this.autoecole,
    });
  }
  ngOnInit(): void {
    this.autoecole = this.data;
    // this.vehicules = this.data.vehicules;

    // if (vehiculesStr) {
    //   this.vehicules = this.convertirEnTableau(vehiculesStr);
    //   console.log(this.vehicules);
    // }
  }

  private convertirEnTableau(jsonString: string): any[] {
    try {
      return JSON.parse(jsonString);
    } catch (error) {
      return [];
    }
  }

  openSuiviModal(url?: string) {
    if (url) {
      this.previewUrl = url;
    }
    $(`#suivi-${this.data.id}`).modal('show');
  }

  assets(path?: string) {
    return environment.autoecole.asset + path;
  }

  getSelectedRestrictionNames(): string {
    const selectedRestrictionNames = this.data.restrictionss.map((r: any) => {
      return r.name;
    });
    return selectedRestrictionNames.join(', ');
  }

  updateAutoEcole(object: any) {
    this.autoecole = {};
    // if (action == 'edit') {
    //   this.auto_ecole_formulaire = "Modification d'une auto-école";
    // } else {
    //   this.auto_ecole_formulaire = "Ajout d'une auto-école";
    // }
    this.autoecole.id = object.id;
    this.autoecole.name = object.name;
    this.autoecole.num_ifu = object.num_ifu;

    this.autoecole.phone = object.phone;
    this.autoecole.email = object.email;
    this.autoecole.adresse = object.adresse;
    this.autoecole.type = object.type;
    this.autoecole.departement_id = object.departement_id;
    //
    // this.autoecole.num_ifu = object.num_ifu;
    // this.autoecole.npi = object.promoteur_info?.npi;
    // this.autoecole.email_promoteur = object.promoteur_info?.email;
    // this.autoecole.agrement_code = object.agrement?.code;
    // this.autoecole.licence_code = object.licences?.[0]?.code;
    // this.autoecole.date_licence = object.licences?.[0]?.date_fin;
    // Formater la date selon vos besoins (exemple : 'dd/MM/yyyy')
    // this.autoecole.date_licence = this.formatDate(
    //   object.licences?.[0]?.date_fin
    // );

    this.communes = this.communesdepart.filter(
      (item: any) => item.departement_id == this.autoecole.departement_id
    );
    this.autoecole.commune_id = object.commune_id;
    // this.action = action;
    $(`#${this.modalEditId}`).modal('show');
  }

  selectDep() {
    this.communes = this.communesdepart.filter(
      (item: any) => item.departement_id == this.autoecole.departement_id
    );
    // this.commune = '';
  }

  saveAutoEcole(event: Event) {
    event.preventDefault();
    this.onLoadingAutoSave = true;
    if (this.autoecole.id) {
      this.postAutoEcole();
    }
  }

  postAutoEcole() {
    this.onLoadingAutoSave = true;
    this.aecoleService
      .updateAutoEcole(this.autoecole)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadingAutoSave = false;
        })
      )
      .subscribe((response) => {
        // this.get();
        $(`#${this.modalEditId}`).modal('hide');
        emitAlertEvent('Auto-école ajoutée avec succès!', 'success');
        this.onLoadingAutoSave = false;
        this.aecoleService.updateAutoEcoleList();
      });
  }

  showMP(subject: any, aeId: number, mp: any) {
    this.subject = '';
    this.moniteur = {};
    this.promoteur = {};
    this.npi = '';
    this.npi = mp.npi;
    this.subject = subject;
    if (subject === 'moniteur') {
      this.mp_formulaire = 'Edition de moniteur';
    } else {
      this.mp_formulaire = 'Edition de promoteur';
    }
    $(`#${this.modalEditMPId}`).modal('show');

    this.mp = {
      id: mp.id,
      auto_ecole_id: aeId,
    };

    // this.action = action;
  }

  findMoniteurByNPI(npi: string, moniteurs_info: any) {
    return moniteurs_info.find((item: any) => item.npi == npi);
  }
  saveMP(event: Event) {
    event.preventDefault();
    this.mp.npi = this.npi;
    this.onLoadingMPSave = true;
    if (this.mp.id) {
      this.updateMP();
    }
  }

  updateMP() {
    this.onLoadingMPSave = true;
    if (this.subject === 'moniteur') {
      this.aecoleService
        .updateMoniteur(this.mp)
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onLoadingMPSave = false;
          })
        )
        .subscribe((response) => {
          // this.get();
          $(`#${this.modalEditMPId}`).modal('hide');
          emitAlertEvent('Mise à jour effectuée avec succès!', 'success');
          this.onLoadingMPSave = false;
          this.aecoleService.updateAutoEcoleList();
        });
    } else {
      this.aecoleService
        .updatePromoteur(this.mp)
        .pipe(
          this.errorHandler.handleServerErrors((response) => {
            this.onLoadingMPSave = false;
          })
        )
        .subscribe((response) => {
          // this.get();
          $(`#${this.modalEditMPId}`).modal('hide');
          emitAlertEvent('Mise à jour effectuée avec succès!', 'success');
          this.onLoadingMPSave = false;
          this.aecoleService.updateAutoEcoleList();
        });
    }
  }

  showVehicule(aeId: number, vehicule: any) {
    console.log(vehicule);
    this.vehicule = {};
    this.immatriculation = '';
    this.immatriculation = vehicule.immatriculation;
    this.vehicule_formulaire = 'Edition de véhicule';
    this.vehicule = {
      id: vehicule.id,
      auto_ecole_id: aeId,
      // immatriculation: vehicule.immatriculation,
    };
    $(`#${this.modalEditVehiculeId}`).modal('show');
  }

  saveVehicue(event: Event) {
    event.preventDefault();
    this.vehicule.immatriculation = this.immatriculation;
    this.onLoadingVehiculeSave = true;
    if (this.vehicule.id) {
      this.updateVehicule();
    }
  }

  updateVehicule() {
    this.onLoadingVehiculeSave = true;
    this.aecoleService
      .updateVehicule(this.vehicule)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadingVehiculeSave = false;
        })
      )
      .subscribe((response) => {
        // this.get();
        $(`#${this.modalEditVehiculeId}`).modal('hide');
        emitAlertEvent('Mise à jour effectuée avec succès!', 'success');
        this.onLoadingVehiculeSave = false;
        this.aecoleService.updateAutoEcoleList();
      });
  }
}
