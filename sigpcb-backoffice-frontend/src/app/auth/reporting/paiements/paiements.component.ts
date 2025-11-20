import { Component } from '@angular/core';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ReportingService } from 'src/app/core/services/reporting.service';

@Component({
  selector: 'app-paiements',
  templateUrl: './paiements.component.html',
  styleUrls: ['./paiements.component.scss'],
})
export class PaiementsComponent {
  paginate_data!: any;
  pageNumber = 1;
  reporting: any[] = [];
  onLoadReporting = true;
  annees: any[] = [];
  // anneeSelected: any = null;
  examensDepart: any[] = [];
  examens: any[] = [];
  /**
   * Les paramètres de filtrage
   */
  filters = {
    anneeSelected: null as string | null,
    sessionSelected: null as number | null,
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
    // this.getCategories();
    this.getExamens();
    // this.getAnnexeAnatt();
  }
  getReporting() {
    this.reporting = [];
    const filters: any = [
      { list: 'paiement' },
      { page: this.pageNumber },
      { annee: this.filters.anneeSelected },
      { examen_id: this.filters.sessionSelected },
    ];
    this.onLoadReporting = true;
    this.reportingService
      .all(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadReporting = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.paginate_data = data.paginate_data;
        this.reporting = this.paginate_data.data;
        this.onLoadReporting = false;
      });
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
  //     { examen_id: this.filters.sessionSelected },
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

  refresh() {
    this.filters.anneeSelected = null;
    this.filters.sessionSelected = null;
    this.getReporting();
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
