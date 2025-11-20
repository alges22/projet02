import { Component } from '@angular/core';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PermisInternationalService } from 'src/app/core/services/permis-international.service';

@Component({
  selector: 'app-validate-permis-inter',
  templateUrl: './validate-permis-inter.component.html',
  styleUrls: ['./validate-permis-inter.component.scss'],
})
export class ValidatePermisInterComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  permis_international: any[] = [];
  onLoadPermisInter = true;
  dossierIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
  };
  categories: any;
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private permisInterService: PermisInternationalService,
    private categoryPermisService: CategoryPermisService
  ) {}

  ngOnInit(): void {
    this.get();
    this.getCategorie();
  }

  get() {
    this.onLoadPermisInter = true;
    this.permis_international = [];
    const states = ['validate'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.permisInterService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadPermisInter = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.permis_international = this.paginate_data.data;
        this.onLoadPermisInter = false;
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
}
