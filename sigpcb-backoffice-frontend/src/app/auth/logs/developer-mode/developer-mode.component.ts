import { Component } from '@angular/core';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { LogService } from 'src/app/core/services/log.service';

@Component({
  selector: 'app-developer-mode',
  templateUrl: './developer-mode.component.html',
  styleUrls: ['./developer-mode.component.scss'],
})
export class DeveloperModeComponent {
  logs: any[] = [];
  filters = {
    search: '',
    page: 1,
  };
  paginate_data: any = {};
  constructor(
    private logService: LogService,
    private errorhandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    this.get();
  }

  get() {
    this.logService
      .get(this.filters)
      .pipe(this.errorhandler.handleServerErrors())
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.logs = this.paginate_data.data;
      });
  }
  paginate(number: number) {
    this.filters.page = number ?? 1;
    this.get();
  }

  paginateArgs() {
    return {
      itemsPerPage: 20,
      currentPage: this.filters.page,
      totalItems: this.paginate_data.total ?? 0,
    };
  }
}
