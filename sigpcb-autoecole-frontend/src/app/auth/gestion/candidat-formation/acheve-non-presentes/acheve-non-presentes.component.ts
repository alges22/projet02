import { Component, Input } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Langue } from 'src/app/core/interfaces/langue';
import { Suivi } from 'src/app/core/interfaces/monitoring';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { MonitoringService } from 'src/app/core/services/monitoring.service';
import { PdfService } from 'src/app/core/services/pdf.service';

@Component({
  selector: 'app-acheve-non-presentes',
  templateUrl: './acheve-non-presentes.component.html',
  styleUrls: ['./acheve-non-presentes.component.scss'],
})
export class AcheveNonPresentesComponent {
  @Input('paginate') canPaginate = true;
  @Input() per_page = 25;
  paginate_data!: any;
  pageNumber = 1;
  onLoadingDossier = true;
  langues: Langue[] = [];
  monitorises: Suivi[] = [];

  langueSelected: null | number = null;

  permis: CategoryPermis[] = [];
  permisSelected: null | number = null;
  search = null as number | null;

  typeExamen: string = 'code-conduite';
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private categoryPermisService: CategoryPermisService,
    private langueService: LangueService,
    private monitoring: MonitoringService,
    private pdf: PdfService
  ) {}

  ngOnInit(): void {
    this._getMonitorises(); // Fetch "dossiers en attente" when the component initializes.
    this._getPermis();
    this._getLangues();
  }

  private _getMonitorises() {
    this.onLoadingDossier = true;
    const filters: any = [
      { state: 'pending' },
      { page: this.pageNumber },
      { cat_permis_id: this.permisSelected },
      { lang_id: this.langueSelected, search: this.search },
      { per_page: this.per_page, search: this.search },
      { type_examen: this.typeExamen },
    ];
    this.monitoring
      .getMonitoringList(filters)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.paginate_data = response.data.paginate_data;
        this.monitorises = this.paginate_data.data;
        this.onLoadingDossier = false;
      });
  }

  private _getPermis() {
    this.categoryPermisService.all().subscribe((response) => {
      this.permis = response.data;
    });
  }

  private _getLangues() {
    this.errorHandler.startLoader();
    this.langueService.all().subscribe((response) => {
      this.langues = response.data;
      this.errorHandler.stopLoader();
    });
  }

  refresh() {
    this._getMonitorises();
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
    this.refresh();
  }
  download() {
    this.errorHandler.startLoader('Téléchargement ...');
    this.pdf
      .candidats([
        {
          state: 'pending',
        },
      ])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }
}
