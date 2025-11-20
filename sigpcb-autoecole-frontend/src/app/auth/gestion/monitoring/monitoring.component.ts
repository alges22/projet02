import { Component, OnInit } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { DossierSession } from 'src/app/core/interfaces/dossier-candidat';
import { Langue } from 'src/app/core/interfaces/langue';
import { Suivi } from 'src/app/core/interfaces/monitoring';
import {
  Ae,
  AutoEcole,
  Promoteur,
} from 'src/app/core/interfaces/user.interface';
import { AeService } from 'src/app/core/services/ae.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ChapitreService } from 'src/app/core/services/chapitre.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { MonitoringService } from 'src/app/core/services/monitoring.service';
import { refresh } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-monitoring',
  templateUrl: './monitoring.component.html',
  styleUrls: ['./monitoring.component.scss'],
})
export class MonitoringComponent implements OnInit {
  per_page = 25;
  paginate_data!: any;
  pageNumber = 1;

  chapitreIds: number[] = []; // les chapites ID sélectionés
  chapitres: any[] = [];
  allSelected = false;
  permis: CategoryPermis[] = [];
  langues: Langue[] = [];
  groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
  permisSelected: null | number = null;
  langueSelected: null | number = null;
  certified = false;

  monitoringData: any | null = null; // sera assigné avec les  propriétés actuelles du component
  dossiersCandidats: DossierSession[] = [];
  dossiersCandidatSelected: number[] = [];
  isLoading = false;
  auth: Promoteur | null = null;
  currentAe: Ae | null = null;
  onLoading = true;

  search = null as number | null;
  searchInput = '';
  constructor(
    private breadcrumb: BreadcrumbService,
    private chapitreService: ChapitreService,
    private categoryPermisService: CategoryPermisService,
    private langueService: LangueService,
    private monitoringService: MonitoringService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private authService: AuthService,
    private aeService: AeService
  ) {}
  ngOnInit(): void {
    this._setBreadcrumbs();
    this._getChapitres();
    this._getPermis();
    this._getLangues();
    this.get();
    this.auth = this.authService.auth();
    this.currentAe = this.aeService.getAe();
  }
  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Suivi des candidats', [
      {
        label: 'Tableau de bord',
        route: '/gestions/home',
      },
      {
        label: 'Suivi des candidats',
        active: true,
      },
    ]);
  }

  private _getChapitres() {
    this.errorHandler.startLoader();
    this.chapitreService.get().subscribe((response) => {
      this.chapitres = response.data;
      this.errorHandler.stopLoader();
    });
  }

  private _getPermis() {
    this.errorHandler.startLoader();
    this.categoryPermisService.all().subscribe((response) => {
      this.permis = response.data;
      this.errorHandler.stopLoader();
    });
  }

  private _getLangues() {
    this.errorHandler.startLoader();
    this.langueService.all().subscribe((response) => {
      this.langues = response.data;
      this.errorHandler.stopLoader();
    });
  }
  appendChapitre(event: Event) {
    const target = event.target as HTMLInputElement;
    const chapitreId = parseInt(target.value, 10);
    if (target.checked) {
      // Ajouter chapitreId s'il n'est pas déjà présent
      if (!this.chapitreIds.includes(chapitreId)) {
        this.chapitreIds.push(chapitreId);
      }
    } else {
      // Supprimer chapitreId
      const index = this.chapitreIds.indexOf(chapitreId);
      if (index !== -1) {
        this.chapitreIds.splice(index, 1);
      }
    }
    this._allSelected();
  }

  toggleAllChapitres(event: any) {
    if (event.target.checked) {
      // Sélectionner tous les chapitres
      this.chapitreIds = this.chapitres.map((chapitre) => chapitre.id);
    } else {
      // Désélectionner tous les chapitres
      this.chapitreIds = [];
    }
    this._allSelected();
  }

  private _allSelected() {
    this.allSelected = this.chapitreIds.length === this.chapitres.length;
  }
  onCertficed(event: any) {
    this.certified = event.target.checked;
  }
  inputChecked(id: number) {
    return this.chapitreIds.includes(id);
  }

  canProcced() {
    return (
      this.certified &&
      this.chapitreIds.length > 0 &&
      this.dossiersCandidatSelected.length > 0 &&
      !this.isLoading
    );
  }

  save(event: Event) {
    event.preventDefault();
    this.isLoading = true;

    // Set the monitoringData object with the current component properties
    this.monitoringData = {
      chapitres_id: this.chapitreIds,
      certification: this.certified,
      dossier_session_id: this.dossiersCandidatSelected,
    };

    // Call the createMonitoring method of the MonitoringService to save the monitoring data
    this.monitoringService
      .createMonitoring(this.monitoringData)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isLoading = false;
        })
      )
      .subscribe((response) => {
        this.errorHandler.emitSuccessAlert(response.message);
        this.isLoading = false;
        this._clear();
        refresh();
      });
  }

  onSelectDossier(event: any) {
    const input = event.target as HTMLInputElement;
    const dossier_id = parseInt(input.value, 10);

    if (input.checked) {
      if (!this.dossiersCandidatSelected.includes(dossier_id)) {
        this.dossiersCandidatSelected.push(dossier_id);
      }
    } else {
      const index = this.dossiersCandidatSelected.indexOf(dossier_id);
      if (index !== -1) {
        this.dossiersCandidatSelected.splice(index, 1);
      }
    }
  }

  /**
   * Récupération les dossiers après pré-inscription
   */
  get() {
    const filters: any = [
      { state: 'init' },
      { page: this.pageNumber },
      { categorie_permis_id: this.permisSelected },
      { langue_id: this.langueSelected, search: this.search },
      { perPage: this.per_page },
    ];
    if (this.searchInput.length >= 3) {
      filters.push({
        search: this.searchInput,
      });
    }
    this.onLoading = true;
    this.candidatService
      .getDossiers(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.paginate_data = data.paginate_data;
        this.onLoading = false;
        this.dossiersCandidats = this.paginate_data.data;
        this.dossiersCandidats = this.dossiersCandidats.map((dc) => {
          const fiche_medical = String(dc.fiche_medical);
          const fiche_groupage = String(dc.dossier.groupage_test);
          let restrictions: any[] = JSON.parse(dc.restriction_medical);
          restrictions = Array.isArray(restrictions) ? restrictions : [];
          dc.canMonitored =
            this.groups.includes(dc.dossier.group_sanguin) &&
            fiche_medical.includes('.') &&
            fiche_groupage.includes('.') &&
            restrictions.length > 0;

          return dc;
        });
      });
  }

  onPermisSelected(event: any) {
    this.permisSelected = Number(event.target.value) || null;
    this.dossiersCandidatSelected = [];
    this.get();
  }

  onLangueSelected(event: any) {
    this.langueSelected = Number(event.target.value) || null;
    this.dossiersCandidatSelected = [];
    this.get();
  }

  private _clear() {
    this.chapitreIds = [];
    this.permisSelected = null;
    this.langueSelected = null;
    this.dossiersCandidatSelected = [];
  }

  paginateArgs() {
    return {
      itemsPerPage: this.per_page,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data?.total ?? 0,
    };
  }
  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.get();
  }
  searchOnKeyup() {
    if (this.searchInput.length >= 3) {
      this.get();
    } else {
      if (this.searchInput.length == 0) {
        this.get();
      }
    }
  }
}
