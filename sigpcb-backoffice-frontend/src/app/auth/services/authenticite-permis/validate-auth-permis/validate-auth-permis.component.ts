import { Component } from '@angular/core';
import { AuthenticitePermis } from 'src/app/core/interfaces/services';
import { AuthenticiteDuPermisService } from 'src/app/core/services/authenticite-du-permis.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-validate-auth-permis',
  templateUrl: './validate-auth-permis.component.html',
  styleUrls: ['./validate-auth-permis.component.scss'],
})
export class ValidateAuthPermisComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  authenticite_permis: AuthenticitePermis[] = [];
  onLoadAuthPermis = true;
  dossierIndex: number | null = null;
  /**
   * Les paramètres de filtrage
   */
  filters = {
    search: null as string | null | number,
  };

  constructor(
    private errorHandler: HttpErrorHandlerService,
    private authPermisService: AuthenticiteDuPermisService,
    private counter: CounterService
  ) {}

  ngOnInit(): void {
    this.get();
  }

  get() {
    this.onLoadAuthPermis = true;
    this.authenticite_permis = [];
    const states = ['validate'];
    const page = this.pageNumber;
    const search = this.filters.search;
    this.authPermisService
      .get(states, page, search)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAuthPermis = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.authenticite_permis = this.paginate_data.data;
        this.onLoadAuthPermis = false;
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
