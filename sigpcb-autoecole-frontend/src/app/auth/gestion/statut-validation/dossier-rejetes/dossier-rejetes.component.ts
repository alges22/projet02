import { Component, Input, OnInit } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { Langue } from 'src/app/core/interfaces/langue';
import { Suivi } from 'src/app/core/interfaces/monitoring';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { MonitoringService } from 'src/app/core/services/monitoring.service';
import { PdfService } from 'src/app/core/services/pdf.service';

@Component({
  selector: 'app-dossier-rejetes',
  templateUrl: './dossier-rejetes.component.html',
  styleUrls: ['./dossier-rejetes.component.scss'],
})
export class DossierRejetesComponent implements OnInit {
  @Input('paginate') canPaginate = true;
  paginate_data!: any;

  onLoadingDossier = true;
  langues: Langue[] = [];
  monitorises: Suivi[] = [];

  permis: CategoryPermis[] = [];
  filters = {
    state: 'rejet',
    page: 1,
    categorie_permis_id: 0,
    langue_id: 0,
    search: '',
    perPage: 25,
    type_examen: null,
    old_ds_rejet_id: null,
    examen_id: 0,
  };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private categoryPermisService: CategoryPermisService,
    private langueService: LangueService,
    private monitoring: MonitoringService,
    private pdf: PdfService,
    private examenService: ExamenService
  ) {}

  ngOnInit(): void {
    this._getMonitorises(); // Fetch "dossiers en attente" when the component initializes.
    this._getPermis();
    this._getLangues();
    this.onExamen();
  }

  private _getMonitorises() {
    this.onLoadingDossier = true;

    this.monitoring.getMonitoringList([this.filters]).subscribe((response) => {
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
      itemsPerPage: this.filters.perPage,
      currentPage: this.filters.page,
      totalItems: this.paginate_data?.total ?? 0,
    };
  }
  paginate(number: number) {
    this.filters.page = number ?? 1;
    this.refresh();
  }

  download() {
    this.errorHandler.startLoader('Téléchargement ...');
    this.pdf
      .candidats([
        {
          state: 'rejet',
        },
      ])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }
  private onExamen() {
    this.examenService.onSelectedExam().subscribe((examen) => {
      let examen_id = 0;
      if (examen) {
        examen_id = examen.id;
      }
      this.filters.examen_id = examen_id;
      this.refresh();
    });
  }
}
