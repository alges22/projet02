import { Component } from '@angular/core';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-candidat-home',
  templateUrl: './candidat-home.component.html',
  styleUrls: ['./candidat-home.component.scss'],
})
export class CandidatHomeComponent {
  candidats: any = [];
  filters = {
    search: '',
    page: 1,
  };
  paginate_data: any = {};
  constructor(
    private candidatService: CandidatService,
    private errorhandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.get();
  }
  get() {
    const filters: any = { ...this.filters };
    if (this.filters.search != '') {
      delete filters.page;
    }
    this.candidatService
      .get(filters)
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.candidats = this.paginate_data.data;
      });
  }

  paginate(number: number) {
    this.filters.page = number ?? 1;
    this.get();
  }

  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.filters.page,
      totalItems: this.paginate_data.total ?? 0,
    };
  }
}
