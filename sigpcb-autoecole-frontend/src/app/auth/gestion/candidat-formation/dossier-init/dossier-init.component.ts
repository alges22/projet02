import { Component, Input, OnInit } from '@angular/core';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { DossierSession } from 'src/app/core/interfaces/dossier-candidat';
import { Langue } from 'src/app/core/interfaces/langue';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LangueService } from 'src/app/core/services/langue.service';
import { PdfService } from 'src/app/core/services/pdf.service';

@Component({
  selector: 'app-dossier-init',
  templateUrl: './dossier-init.component.html',
  styleUrls: ['./dossier-init.component.scss'],
})
export class DossierInitComponent implements OnInit {
  paginate_data!: any;
  pageNumber = 1;
  @Input('paginate') canPaginate = true;
  @Input() per_page = 25;
  onLoadingDossier = true;
  langues: Langue[] = [];
  candidats: DossierSession[] = [];

  langueSelected: null | number = null;

  permis: CategoryPermis[] = [];
  permisSelected: null | number = null;
  search = null as number | null;

  typeExamen: string = 'code-conduite';
  constructor(
    private candidatService: CandidatService,

    private categoryPermisService: CategoryPermisService,
    private langueService: LangueService,
    private errorHandler: HttpErrorHandlerService,
    private pdf: PdfService
  ) {}

  ngOnInit(): void {
    this._getInitDossiers(); // Fetch "dossiers en cours" when the component initializes.
    this._getPermis();
    this._getLangues();
  }

  private _getInitDossiers() {
    const filters: any = [
      { state: 'init' },
      { page: this.pageNumber },
      { categorie_permis_id: this.permisSelected },
      { langue_id: this.langueSelected, search: this.search },
      { per_page: this.per_page, search: this.search },
      { type_examen: this.typeExamen },
    ];
    this.onLoadingDossier = true;
    this.candidatService
      .getDossiers(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadingDossier = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data.paginate_data;
        this.candidats = this.paginate_data.data ?? [];
        this.onLoadingDossier = false;
      });
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
    this._getInitDossiers();
  }
  private _getPermis() {
    this.categoryPermisService.all().subscribe((response) => {
      this.permis = response.data;
    });
  }

  refresh() {
    this._getInitDossiers();
  }

  private _getLangues() {
    this.errorHandler.startLoader();
    this.langueService.all().subscribe((response) => {
      this.langues = response.data;
      this.errorHandler.stopLoader();
    });
  }

  download() {
    this.errorHandler.startLoader('Téléchargement ...');
    this.pdf
      .candidats([
        {
          state: 'init',
        },
      ])
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        window.open(response.data, '_blank');
      });
  }
}
