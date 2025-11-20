import { Component } from '@angular/core';
import { RecrutementEntreprise } from 'src/app/core/interfaces/recreutement';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { RecrutemmentExaminateurService } from 'src/app/core/services/recrutemment-examinateur.service';
import { emitAlertEvent, toFormData } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-entreprise-conducteur',
  templateUrl: './entreprise-conducteur.component.html',
  styleUrls: ['./entreprise-conducteur.component.scss'],
})
export class EntrepriseConducteurComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  entreprises: RecrutementEntreprise[] = [];
  entreprieForm = {} as RecrutementEntreprise;
  onLoadEntreprise = true;
  entrepriseIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null,
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

  constructor(
    private errorHandler: HttpErrorHandlerService,
    private recrutementExaminateurService: RecrutemmentExaminateurService,
    private counter: CounterService,
    private categoryPermisService: CategoryPermisService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getCategorie();
  }

  get() {
    this.onLoadEntreprise = true;
    const states = ['validate'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.recrutementExaminateurService
      .getEntreprises(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadEntreprise = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.entreprises = this.paginate_data.data;
        this.onLoadEntreprise = false;
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

  showEntreprise(i: number): void {
    if (this.entrepriseIndex === i) {
      this.entrepriseIndex = null;
    } else {
      this.entrepriseIndex = i;
    }
  }
  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data.total ?? 0,
    };
  }

  // validate(): void {
  //   this.errorHandler.startLoader('Validation en cours ...');
  //   this.recrutementExaminateurService
  //     .validate(this.data)
  //     .pipe(this.errorHandler.handleServerErrors())
  //     .subscribe((response) => {
  //       emitAlertEvent(`Demande validée  avec succès.`, 'success', 'middle');
  //       this.errorHandler.stopLoader();
  //       this.entrepriseIndex = null;
  //       $('#decision-modal').modal('hide');
  //       this.counter.refreshCount();
  //       this.get();
  //     });
  // }

  edit(entreprise?: RecrutementEntreprise) {
    if (entreprise) {
      this.entreprieForm = entreprise;
    }
    $('#add-corporate').modal('show');
  }

  private _add() {
    this.errorHandler.startLoader('Enregistrement en cours ...');
    this.recrutementExaminateurService
      .addEntreprise(toFormData(this.entreprieForm))
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success', 'middle');
        this.errorHandler.stopLoader();
        this.entrepriseIndex = null;
        $('#add-corporate').modal('hide');
        this.counter.refreshCount();
        this.get();
      });
  }

  private _update() {
    if (this.entreprieForm) {
      this.errorHandler.startLoader('Mise à jour en cours ...');
      this.recrutementExaminateurService
        .addEntreprise(this.entreprieForm, this.entreprieForm.id)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          emitAlertEvent(response.message, 'success', 'middle');
          this.errorHandler.stopLoader();
          this.entrepriseIndex = null;
          $('#add-corporate').modal('hide');
          this.counter.refreshCount();
          this.get();
        });
    }
  }

  save() {
    if (!this.entreprieForm.id) {
      this._add();
    } else {
      this._update();
    }
  }

  destroy(id: number) {
    this.errorHandler.startLoader('Suppression en cours ...');
    this.recrutementExaminateurService
      .deleteEntreprise(id)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        emitAlertEvent(response.message, 'success');
        this.counter.refreshCount();
        this.errorHandler.stopLoader();
      });
  }
}
