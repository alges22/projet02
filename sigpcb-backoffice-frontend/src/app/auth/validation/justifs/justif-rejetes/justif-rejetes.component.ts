import { Component } from '@angular/core';
import { AnnexeAnatt } from 'src/app/core/interfaces/annexe-anatt';
import { CategoryPermis } from 'src/app/core/interfaces/catgory-permis';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { CategoryPermisService } from 'src/app/core/services/category-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ValidationService } from 'src/app/core/services/validation.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
@Component({
  selector: 'app-justif-rejetes',
  templateUrl: './justif-rejetes.component.html',
  styleUrls: ['./justif-rejetes.component.scss'],
})
export class JustifRejetesComponent {
  justifIndex: number | null = null;
  justifications: any[] = [];
  onLoadValidation = true;

  paginate_data!: any;
  pageNumber = 1;
  categories: CategoryPermis[] = []; //
  /**
   * Les paramètres de filtrage
   */
  filters = {
    permisSelected: null as number | null,
    annexeSelected: null as number | null,
    sessionSelected: null as number | null,
    search: null as number | null,
  };

  annexeAnatts: AnnexeAnatt[] = [];
  examens: any[] = [];

  constructor(
    private validationService: ValidationService,
    private categoryPermisService: CategoryPermisService,
    private errorHandler: HttpErrorHandlerService,
    private annexeAnattService: AnnexeAnattService,
    private examenService: ExamenService,
    private counter: CounterService
  ) {}
  ngOnInit(): void {
    this.getJustifications();
    this.getCategories();
    this.getExamens();
    this.getAnnexeAnatt();
  }
  showDossier(i: number): void {
    if (this.justifIndex === i) {
      this.justifIndex = null;
    } else {
      this.justifIndex = i;
    }
  }

  onValidate(event: any, index: number): void {
    if (event.state === 'validate') {
      this.errorHandler.startLoader('Validation en cours ...');
      this.validationService
        .validate(event.justifId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          emitAlertEvent(
            `Vous avez validé le dossier de <b>${event.candidat?.nom} et ${event.candidat?.prenoms}</b>  avec succès. Le dossier suivant vous est ouvert`,
            'success',
            'middle'
          );
          this.errorHandler.stopLoader();
          this.justifications = this.justifications.filter(
            (justification) => justification.id !== event.justifId
          );
          this.counter.refreshCount();
          this.getJustifications();
          this.justifIndex = index + 1;
        });
    }
  }

  /**
   * Obtenir les annexes Anatt.
   */

  private getAnnexeAnatt() {
    this.annexeAnattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.annexeAnatts = response.data;
      });
  }

  private getExamens() {
    this.examenService
      .getExemens()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.examens = response.data;
      });
  }
  /**
   * Obtenez les données de surveillance.
   *
   * Filtres @param Les filtres à appliquer à la requête.
   */
  getJustifications() {
    const filters: any = [
      { list: 'rejet' },
      { page: this.pageNumber },
      { annexe_id: this.filters.annexeSelected },
      { categorie_permis_id: this.filters.permisSelected },
      { examen_id: this.filters.sessionSelected, search: this.filters.search },
    ];
    this.onLoadValidation = true;
    this.validationService
      .all(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadValidation = false;
        })
      )
      .subscribe((response) => {
        const data = response.data;
        this.paginate_data = data.paginate_data;
        this.justifications = this.paginate_data.data;
        this.onLoadValidation = false;
      });
  }
  /**
   * Obtenir les catégories de permis.
   */
  getCategories() {
    this.categoryPermisService
      .all()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categories = response.data;
      });
  }

  getPermis(category_id: number, def = '') {
    let permis = this.categories.filter((c) => c.id === category_id)[0];
    if (permis) {
      return permis;
    }
    return {
      name: '',
    };
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
    this.getJustifications();
  }
}
