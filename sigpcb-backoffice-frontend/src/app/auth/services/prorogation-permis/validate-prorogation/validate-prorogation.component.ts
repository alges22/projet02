import { Component } from '@angular/core';
import { ProrogationPermis } from 'src/app/core/interfaces/services';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ProrogationPermisService } from 'src/app/core/services/prorogation-permis.service';

@Component({
  selector: 'app-validate-prorogation',
  templateUrl: './validate-prorogation.component.html',
  styleUrls: ['./validate-prorogation.component.scss'],
})
export class ValidateProrogationComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  prorogation_permis: ProrogationPermis[] = [];
  onLoadProrogation = true;
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
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private echangePermisService: ProrogationPermisService,
    private counter: CounterService,
    private categoryPermisService: CategoryPermisService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadProrogation = true;
    this.prorogation_permis = [];
    const states = ['validate'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.echangePermisService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadProrogation = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.prorogation_permis = this.paginate_data.data;
        this.onLoadProrogation = false;
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
}
