import { Component } from '@angular/core';
import { Moniteur } from 'src/app/core/interfaces/recreutement';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutementMoniteurService } from 'src/app/core/services/recrutement-moniteur.service';
import { RoleService } from 'src/app/core/services/role.service';
import { TitreService } from 'src/app/core/services/titre.service';
import { UniteAdminService } from 'src/app/core/services/unite-admin.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-validate-moniteur',
  templateUrl: './validate-moniteur.component.html',
  styleUrls: ['./validate-moniteur.component.scss'],
})
export class ValidateMoniteurComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  moniteurs: Moniteur[] = [];
  onLoadMoniteur = true;
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

  data = {
    first_name: '',
    last_name: '',
    phone: '',
    role_id: '',
    titre_id: '',
    unite_admin_id: '',
    demande_moniteur_id: 0,
  };
  constructor(
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly recrutementMoniteurService: RecrutementMoniteurService,
    private readonly counter: CounterService,
    private readonly categoryPermisService: CategoryPermisService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getCategorie();
  }

  get() {
    this.onLoadMoniteur = true;
    this.moniteurs = [];
    const states = ['validate'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.recrutementMoniteurService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadMoniteur = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.moniteurs = this.paginate_data.data;
        this.onLoadMoniteur = false;
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

  // onValidate(event: { data: Moniteur; state: string }, index: number): void {
  //   this.decisionData.state = event.state;
  //   this.data.first_name = event.data?.demandeur_info.prenoms;
  //   this.data.last_name = event.data?.demandeur_info.nom;
  //   this.data.phone = event.data?.demandeur_info.telephone;
  //   this.data.first_name = event.data?.demandeur_info.prenoms;
  //   this.data.demande_moniteur_id = event.data.id;
  //   if (event.state === 'validate') {
  //     this.decisionData.title = `Validation de demande pour devenir moniteur de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
  //     this.decisionData.demandeId = event.data.id;

  //     $('#decision-modal').modal('show');
  //   } else if (event.state === 'rejected') {
  //     this.decisionData.title = `Rejet de demande pour devenir moniteur de <span class="text-uppercase">  ${event.data.demandeur_info.nom} ${event.data.demandeur_info.prenoms}</span>`;
  //     this.decisionData.demandeId = event.data.id;
  //     $('#decision-modal').modal('show');
  //   }
  // }

  onValidate(event: any, index: number): void {
    if (event.state === 'validate') {
      this.errorHandler.startLoader('Validation en cours ...');
      console.log(event);
      // this.recrutementMoniteurService
      //   .validate(event.moniteurId)
      //   .pipe(this.errorHandler.handleServerErrors())
      //   .subscribe((response) => {
      //     emitAlertEvent(
      //       `Vous avez validé le dossier de <b>${event.candidat?.nom} ${event.candidat?.prenoms}</b>  avec succès.`,
      //       'success',
      //       'middle'
      //     );
      //     this.errorHandler.stopLoader();
      //     // this.monitoring = this.monitoring.filter(
      //     //   (monitoring) => monitoring.id !== event.suiviId
      //     // );
      //     this.counter.refreshCount();
      //     this.get();
      //     this.dossierIndex = index + 1;
      //   });
    } else if (event.state === 'rejected') {
      // this.rejectData.title = `Rejet de <span class="text-uppercase"> ${event.candidat?.nom} ${event.candidat?.prenoms}</span>`;
      // this.rejectData.suiviId = event.suiviId;
      // $('#rejet-modal').modal('show');
    }
  }

  validate(): void {
    this.errorHandler.startLoader('Validation en cours ...');
    this.recrutementMoniteurService
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
