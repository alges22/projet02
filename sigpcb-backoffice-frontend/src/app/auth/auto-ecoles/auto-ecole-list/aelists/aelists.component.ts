import { Component, SimpleChanges } from '@angular/core';
import { Commune } from 'src/app/core/interfaces/commune';
import { Departement } from 'src/app/core/interfaces/departement';
import { ModalActions } from 'src/app/core/interfaces/modal-actions';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { CommuneService } from 'src/app/core/services/commune.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-aelists',
  templateUrl: './aelists.component.html',
  styleUrls: ['./aelists.component.scss'],
})
export class AelistsComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  autoecoles: any[] = [];
  onLoadAutoEcole = true;
  dossierIndex: number | null = null;
  motif: any;
  data: any;
  private inputElement!: HTMLInputElement;
  hasChange: boolean = false;
  activateId: number = 0;
  checked: boolean = false;
  action: any;
  autoecoleActive: any = 'all';
  modalImportId = 'import';
  modalAddId = 'add';
  importFile: any;
  importFileName = 'import-model-auto-ecole.xlsx';
  onLoadingImport = false;
  onLoadingSave = false;
  autoecole = {} as any;
  auto_ecole_formulaire = "Ajout d'une auto-école";
  departements = [] as Departement;
  departementsEmplacements: any[] = [];
  communesdepart = [] as Commune[];
  communes = [] as Commune[];
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
  };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private aecoleService: AecoleService,
    private counter: CounterService,
    private departementService: DepartementService,
    private communeService: CommuneService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getDepartements();
    this.getCommunes();
    this.aecoleService.updateAutoEcoleObservable.subscribe(() => {
      this.get();
    });
  }

  get() {
    this.onLoadAutoEcole = true;
    this.autoecoles = [];
    const filters: any = [
      { status: this.autoecoleActive },
      { page: this.pageNumber },
      { search: this.filters.search },
    ];
    this.aecoleService
      .get(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAutoEcole = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.autoecoles = this.paginate_data.data;
        this.onLoadAutoEcole = false;
      });
  }

  desactive() {
    if (this.action != 'cancel') {
      this.errorHandler.startLoader();
      this.aecoleService
        .status({
          auto_ecole_id: this.activateId,
          motif: this.motif,
          status: this.checked,
        })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          if (response.status) {
            this.errorHandler.stopLoader();
            const content = this.checked ? 'activée' : 'désactivée';
            emitAlertEvent(
              `L'auto-école a été ${content} avec succès.`,
              'success',
              'middle'
            );
            $('#motif-modal').modal('hide');
          }
        });
      this.motif = '';
    }
  }

  cancel() {
    if (this.hasChange) {
      this.action = 'cancel';
      this.inputElement.click();
      this.hasChange = false;
    }
    $('#motif-modal').modal('hide');
    this.action = '';
    this.motif = '';
  }

  switch(activateId: number, event: Event) {
    this.activateId = activateId;
    this.hasChange = true;
    this.inputElement = event.target as HTMLInputElement;
    this.checked = this.inputElement.checked;
    if (!this.inputElement.checked) {
      $('#motif-modal').modal('show');
    } else {
      this.desactive();
    }
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }

  showDossier(i: number): void {
    if (this.dossierIndex === i) {
      this.dossierIndex = null;
    } else {
      this.dossierIndex = i;
    }
  }

  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data.total ?? 0,
    };
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
    this.aecoleService
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
        this.get();
      });
  }

  openModal(action: ModalActions, object?: any) {
    // this.langue = {} as Langue;
    if (action == 'edit') {
      this.auto_ecole_formulaire = "Modification d'une auto-école";
    } else {
      this.auto_ecole_formulaire = "Ajout d'une auto-école";
    }
    if (object) {
      this.autoecole = object;
      this.autoecole.npi = object.promoteur_info?.npi;
      this.autoecole.email_promoteur = object.promoteur_info?.email;
      this.autoecole.agrement_code = object.agrement?.code;
      this.autoecole.licence_code = object.licences?.[0]?.code;
      // this.autoecole.date_licence = object.licences?.[0]?.date_fin;
      // Formater la date selon vos besoins (exemple : 'dd/MM/yyyy')
      this.autoecole.date_licence = this.formatDate(
        object.licences?.[0]?.date_fin
      );

      this.communes = this.communesdepart.filter(
        (item) => item.departement_id == this.autoecole.departement_id
      );
      if (object.moniteurs.length > 0)
        this.autoecole.moniteurs = object.moniteurs
          .map((item: any) => item.npi)
          .join(', ');
    }
    this.action = action;
    $(`#${this.modalAddId}`).modal('show');
  }

  // Fonction pour formater la date
  formatDate(date: Date): string {
    const options: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      timeZone: 'UTC', // Assurez-vous de définir le fuseau horaire correct si nécessaire
    };
    return date.toLocaleDateString('fr-FR', options);
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
    // this.loadingForList = true;
    this.communeService
      .getCommunes(-1, 'all')
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          // this.loadingForList = false;
          this.errorHandler.stopLoader();
        })
      )
      .subscribe((response) => {
        // this.loadingForList = false;
        if (response.status) {
          this.communesdepart = response.data;
        }
      });
  }

  selectDep() {
    this.communes = this.communesdepart.filter(
      (item) => item.departement_id == this.autoecole.departement_id
    );
    // this.commune = '';
  }

  save(event: Event) {
    event.preventDefault();
    this.onLoadingSave = true;
    if (this.autoecole.id) {
      // this.update();
    } else {
      this.postAutoEcole();
    }
  }

  postAutoEcole() {
    this.onLoadingSave = true;
    this.aecoleService
      .postAutoEcole(this.autoecole)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadingSave = false;
        })
      )
      .subscribe((response) => {
        this.get();
        $(`#${this.modalAddId}`).modal('hide');
        // emitAlertEvent('Auto-école ajoutée avec succès!', 'success');
        this.onRefresh('refresh', 1);
        this.onLoadingSave = false;
      });
  }

  onRefresh(event: any, index: number): void {
    if (event.state === 'refresh') {
      console.log('hhihihi');
      // this.errorHandler.startLoader('Validation en cours ...');
      // const data = {
      //   d_licence_id: event.nouvellelicenceId,
      //   autoecole_name: event?.nouvellelicence?.autoecole.name,
      //   email_promoteur: event?.nouvellelicence?.promoteur_info.email,
      // };
      // this.aecoleService
      //   .validateNouvelleLicence(data)
      //   .pipe(this.errorHandler.handleServerErrors())
      //   .subscribe((response) => {
      //     emitAlertEvent(
      //       `Vous avez validé la demande de licence de l'auto école <b>${event.nouvellelicence?.autoecole.name}</b>  avec succès.`,
      //       'success',
      //       'middle'
      //     );
      //     this.errorHandler.stopLoader();
      //     this.newlicences = this.newlicences.filter(
      //       (ae) => ae.id !== event.nouvellelicenceId
      //     );
      //     this.counter.refreshCount();
      //     this.get();
      //     this.dossierIndex = index + 1;
      //   });
    }
    // else if (event.state === 'rejected') {
    //   this.rejectData.title = `Rejet de demande de licence pour l'auto école <span class="text-uppercase"> ${event.nouvellelicence?.autoecole.name}</span>`;
    //   this.rejectData.nouvellelicenceId = event.nouvellelicenceId;
    //   $('#rejet-modal').modal('show');
    // }
  }

  ngOnChanges(changes: SimpleChanges) {
    // Cette fonction sera appelée lorsqu'il y a des changements dans les propriétés d'entrée
    console.log('Changements détectés :', changes);
    // Effectuez vos actions ici
  }
}
