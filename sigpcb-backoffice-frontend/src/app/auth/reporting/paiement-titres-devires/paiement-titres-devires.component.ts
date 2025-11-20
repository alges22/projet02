import { Component } from '@angular/core';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ReportingService } from 'src/app/core/services/reporting.service';

@Component({
  selector: 'app-paiement-titres-devires',
  templateUrl: './paiement-titres-devires.component.html',
  styleUrls: ['./paiement-titres-devires.component.scss'],
})
export class PaiementTitresDeviresComponent {
  paginate_data!: any;
  pageNumber = 1;
  reporting: any[] = [];
  onLoadReporting = true;
  annees: any[] = [];
  titres = [
    {
      id: 1,
      name: 'Authenticité du permis',
      slug: 'authenticite',
    },
    {
      id: 2,
      name: 'Duplicata du permis',
      slug: 'duplicata',
    },
    {
      id: 3,
      name: 'Permis international',
      slug: 'permis-international',
    },
    {
      id: 4,
      name: 'Echange de permis',
      slug: 'echange-permis',
    },
    {
      id: 5,
      name: 'Prorogation de permis',
      slug: 'prorogation-permis',
    },
  ];
  // anneeSelected: any = null;
  examensDepart: any[] = [];
  examens: any[] = [];
  /**
   * Les paramètres de filtrage
   */
  filters = {
    titreSelected: null as string | null,
    anneeSelected: null as string | null,
    search: null as number | null,
  };
  constructor(
    private reportingService: ReportingService,
    // private categoryPermisService: CategoryPermisService,
    private examenService: ExamenService,
    private errorHandler: HttpErrorHandlerService // private annexeAnattService: AnnexeAnattService, // private examenService: ExamenService, // private counter: CounterService
  ) {}
  ngOnInit(): void {
    for (let i = 2023; i <= 2040; i++) {
      this.annees.push({ annee: i });
    }
    this.getReporting();
  }
  getReporting() {
    this.reporting = [];
    const filters: any = [
      { list: 'paiement' },
      { page: this.pageNumber },
      { titre: this.filters.titreSelected },
      { annee: this.filters.anneeSelected },
    ];
    this.onLoadReporting = true;
    this.reportingService
      .titresDerives(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadReporting = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.reporting = this.paginate_data.data;
        this.onLoadReporting = false;
      });
  }

  refresh() {
    this.filters.titreSelected = null;
    this.filters.anneeSelected = null;
    this.getReporting();
  }

  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.pageNumber,
      totalItems: this.paginate_data?.total ?? 0,
    };
  }

  paginate(number: number) {
    this.pageNumber = number ?? 1;
    this.getReporting();
  }

  // filter() {
  //   const filters: any = [
  //     { list: 'paiement' },
  //     { page: this.pageNumber },
  //     { agregateur: this.filters.agregateurSelected },
  //   ];
  //   this.onLoadReporting = true;
  //   this.reportingService
  //     .all(filters)
  //     .pipe(
  //       this.errorHandler.handleServerErrors((response) => {
  //         this.onLoadReporting = false;
  //       })
  //     )
  //     .subscribe((response) => {
  //       const data = response.data;
  //       this.paginate_data = data.paginate_data;
  //       this.reporting = this.paginate_data.data;
  //       this.onLoadReporting = false;
  //     });
  // }

  selectAnnee(annee: any): void {
    this.examens = this.examensDepart.filter(
      (examen) => examen.annee === annee
    );
  }

  private getExamens() {
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examensDepart = response.data;
        console.log(this.examensDepart);
      });
  }
}
