import { Component } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { DossierSession } from 'src/app/core/interfaces/dossier-candidat';
import { Langue } from 'src/app/core/interfaces/langue';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { PdfService } from 'src/app/core/services/pdf.service';
@Component({
  selector: 'app-candidats',
  templateUrl: './candidats.component.html',
  styleUrls: ['./candidats.component.scss'],
})
export class CandidatsComponent {
  examens: any[] = [];
  paginate_data: any = {};
  permis: CategoryPermis[] = [];
  langues: Langue[] = [];

  candidats: DossierSession[] = [];

  onLoading = true;

  counts: Record<string, number> = {};

  filters = {
    categorie_permis_id: null,
    langue_id: null,
    search: null,
    page: 1,
    per_page: 25,
    year: null,
    type_examen: 'code-conduite',
    examen_id: null,
    resultat_conduite: null,
  };
  constructor(
    private breadcrumb: BreadcrumbService,
    private categoryPermisService: CategoryPermisService,
    private langueService: LangueService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private examenService: ExamenService,
    private counter: CounterService,
    private pdf: PdfService
  ) {}
  ngOnInit(): void {
    this.counter
      .authCount(['ds_c'])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.counts = response.data;
      });
    this._setBreadcrumbs();
    this._getPermis();
    this._getLangues();
    this._getDossierCandidats();
    this.getExamens();
  }
  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs('Les candidats', [
      {
        label: 'Tableau de bord',
        route: '/gestions/home',
      },
      {
        label: 'Les candidats',
        active: true,
      },
    ]);
  }

  private _getPermis() {
    this.categoryPermisService.all().subscribe((response) => {
      this.permis = response.data;
    });
  }

  private _getLangues() {
    this.langueService.all().subscribe((response) => {
      this.langues = response.data;
    });
  }

  /**
   * Récupération les dossiers après pré-inscription
   */
  private _getDossierCandidats() {
    const filters: any = [this.filters];
    this.onLoading = true;
    this.candidatService
      .getCandidats(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoading = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.paginate_data = data.paginate_data;
        this.onLoading = false;
        this.candidats = this.paginate_data.data;
      });
  }

  paginateArgs() {
    return {
      itemsPerPage: this.filters.per_page,
      currentPage: this.filters.page,
      totalItems: this.paginate_data?.total ?? 0,
    };
  }
  private getExamens() {
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
      });
  }
  fresh() {
    this._getDossierCandidats();
  }
  paginate(number: number) {
    this.filters.page = number ?? 1;
    this._getDossierCandidats();
  }

  download() {
    this.errorHandler.startLoader('Téléchargement ...');
    this.pdf
      .candidats([
        {
          examen_id: this.filters.examen_id,
        },
      ])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }
}
