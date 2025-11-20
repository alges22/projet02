import { Component } from '@angular/core';
import { Examinateur } from 'src/app/core/interfaces/recreutement';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { RoleService } from 'src/app/core/services/role.service';
import { TitreService } from 'src/app/core/services/titre.service';
import { UniteAdminService } from 'src/app/core/services/unite-admin.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-rejet-examinateur',
  templateUrl: './rejet-examinateur.component.html',
  styleUrls: ['./rejet-examinateur.component.scss'],
})
export class RejetExaminateurComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  examinateurs: Examinateur[] = [];
  onLoadExaminateur = true;
  dossierIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
  };
  /**
   * Les données du rejet
   */
  decisionData = {
    title: '',
    consigne: '',
    demandeId: 0,
    state: '',
  };
  categories: any;
  uadmins: any;
  titres: any;
  roles: any;
  data = {
    first_name: '',
    last_name: '',
    phone: '',
    role_id: '',
    titre_id: '',
    unite_admin_id: '',
    demande_examinateur_id: 0,
  };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private recrutementExaminateurService: RecrutemmentExaminateurService,
    private counter: CounterService,
    private categoryPermisService: CategoryPermisService,
    private uadminService: UniteAdminService,
    private titreService: TitreService,
    private roleService: RoleService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getCategorie();
    this.getUnitesAdmins();
    this.getTitres();
    this.getRoles();
  }

  get() {
    this.onLoadExaminateur = true;
    this.examinateurs = [];
    const states = ['rejected'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.recrutementExaminateurService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadExaminateur = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.examinateurs = this.paginate_data.data;
        this.onLoadExaminateur = false;
      });
  }

  getCategorie() {
    this.errorHandler.startLoader();
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
        this.errorHandler.stopLoader();
      });
  }

  private getUnitesAdmins() {
    this.errorHandler.startLoader();
    this.uadminService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.uadmins = response.data.filter((u: any) => u.status);
          this.errorHandler.stopLoader();
        }
      });
  }

  private getTitres() {
    this.errorHandler.startLoader();
    this.titreService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.titres = response.data.filter((t: any) => t.status);
          this.errorHandler.stopLoader();
        }
      });
  }

  private getRoles() {
    this.errorHandler.startLoader();
    this.roleService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.roles = response.data;
          this.errorHandler.stopLoader();
        }
      });
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

  onValidate(event: { data: Examinateur; state: string }, index: number): void {
    this.decisionData.state = event.state;
    this.data.first_name = event.data?.demandeur_info.prenoms;
    this.data.last_name = event.data?.demandeur_info.nom;
    this.data.phone = event.data?.demandeur_info.telephone;
    this.data.first_name = event.data?.demandeur_info.prenoms;
    this.data.demande_examinateur_id = event.data.id;
    if (event.state === 'validate') {
      this.decisionData.title = `Validation de demande pour devenir examinateur de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
      this.decisionData.demandeId = event.data.id;

      $('#decision-modal').modal('show');
    } else if (event.state === 'rejected') {
      this.decisionData.title = `Rejet de demande pour devenir examinateur de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
      this.decisionData.demandeId = event.data.id;
      $('#decision-modal').modal('show');
    }
  }

  validate(): void {
    this.errorHandler.startLoader('Validation en cours ...');
    this.recrutementExaminateurService
      .validate(this.data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(`Demande validée  avec succès.`, 'success', 'middle');
        this.errorHandler.stopLoader();
        this.dossierIndex = null;
        $('#decision-modal').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }
}
