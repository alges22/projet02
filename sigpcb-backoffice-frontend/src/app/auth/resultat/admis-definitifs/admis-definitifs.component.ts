import { Component } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { BreadcrumbService } from 'src/app/core/services/breadcrumb.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { PdfService } from 'src/app/core/services/pdf.service';
import { ResultatService } from 'src/app/core/services/resultat.service';

@Component({
  selector: 'app-admis-definitifs',
  templateUrl: './admis-definitifs.component.html',
  styleUrls: ['./admis-definitifs.component.scss'],
})
export class AdmisDefinitifsComponent {
  total = 0;
  filters = {
    categorie_permis_id: null as number | null,
    permis_extension_id: null as number | null,
    annexe_id: null as number | null,
    examen_id: null as number | null,
    search: null as number | null,
    page: 1,
  };

  liste: {
    candidat: any;
    categorie_permis: CategoryPermis;
    npi: string;
    delivered_at: string;
    expired_at: string;
    code: string;
  }[] = [];

  categories: CategoryPermis[] = []; //

  constructor(
    private categoryPermisService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService,
    private annexeService: AnnexeAnattService,
    private examenService: ExamenService,
    private resultatService: ResultatService,
    private pdfService: PdfService,
    private breadcrumb: BreadcrumbService
  ) {}

  ngOnInit(): void {
    this._setBreadcrumbs();
    this.getResultats();
    this.getCategories();

    this._annexeChanged();
    this._sessionChanged();
  }
  getResultats() {
    this.errorHandler.startLoader();
    this.resultatService
      .getAdmisDefinitifs(this.getFilters())

      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.liste = response.data.data;

        this.total = response.data.total;
        this.errorHandler.stopLoader();
      });
  }

  filter() {
    this.getResultats();
  }

  getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
      });
  }

  download(): void {
    this.errorHandler.startLoader('Téléchargement en cours');
    this.pdfService
      .download('resultat-permis-excel', this.filters)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }

  private _annexeChanged() {
    this.annexeService.onAnnexeChange().subscribe((annexe) => {
      if (annexe) {
        this.filters.annexe_id = annexe.id;
      } else {
        this.filters.annexe_id = null;
      }
      this.filter();
    });
  }

  private _sessionChanged() {
    this.examenService.currentSession().subscribe((session) => {
      //Si une session est en cours
      if (session) {
        this.filters.examen_id = session.id;
      } else {
        this.filters.examen_id = null;
      }
      this.filter();
    });
  }

  private getFilters() {
    const record: Record<string, any> = {};

    for (const key in this.filters) {
      if (Object.prototype.hasOwnProperty.call(this.filters, key)) {
        const element = (this.filters as any)[key];
        if (element) {
          record[key] = element;
        }
      }
    }

    return record;
  }

  paginate(number: number) {
    this.filters.page = number ?? 1;
    this.filter();
  }
  get paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.filters.page,
      totalItems: this.total ?? 0,
    };
  }

  private _setBreadcrumbs() {
    this.breadcrumb.setBreadcrumbs(`Transmissions`, [
      {
        label: 'Tableau de bord',
        route: '/dashboard',
      },
      {
        label: `Transmissions`,
        active: true,
      },
    ]);
  }
}
