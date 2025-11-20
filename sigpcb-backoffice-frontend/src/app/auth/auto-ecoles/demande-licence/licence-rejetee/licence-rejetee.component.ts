import { Component } from '@angular/core';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-licence-rejetee',
  templateUrl: './licence-rejetee.component.html',
  styleUrls: ['./licence-rejetee.component.scss'],
})
export class LicenceRejeteeComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  newlicences: any[] = [];
  onLoadNouvelleLicence = true;
  dossierIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
  };
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private aecoleService: AecoleService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadNouvelleLicence = true;
    this.newlicences = [];
    const states = ['rejected'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.aecoleService
      .getNouvelleLicence(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadNouvelleLicence = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.newlicences = this.paginate_data.data;
        this.onLoadNouvelleLicence = false;
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
