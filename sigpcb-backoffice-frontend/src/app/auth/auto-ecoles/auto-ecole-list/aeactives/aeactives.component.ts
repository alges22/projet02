import { Component } from '@angular/core';
import { AecoleService } from 'src/app/core/services/aecole.service';
import { CounterService } from 'src/app/core/services/counter.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-aeactives',
  templateUrl: './aeactives.component.html',
  styleUrls: ['./aeactives.component.scss'],
})
export class AeactivesComponent {
  pageNumber = 1;
  paginate_data: any = {};
  ready = true;
  autoecoles: any[] = [];
  onLoadAutoEcole = true;
  dossierIndex: number | null = null;
  motif: any;
  data: any;
  private inputElement!: HTMLInputElement;
  hasChange: boolean = false;
  activateId: number = 0;
  checked: boolean = false;
  action: any;
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
    this.onLoadAutoEcole = true;
    this.autoecoles = [];
    const filters: any = [
      { status: 'true' },
      { page: this.pageNumber },
      { search: this.filters.search },
    ];
    this.aecoleService
      .getLicence(filters)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.onLoadAutoEcole = false;
        })
      )
      .subscribe((response) => {
        this.paginate_data = response.data;
        this.autoecoles = this.paginate_data.data;
        this.onLoadAutoEcole = false;
      });
  }

  desactive() {
    if (this.action != 'cancel') {
      this.errorHandler.startLoader();
      this.aecoleService
        .status({
          auto_ecole_id: this.activateId,
          motif: this.motif,
          status: this.checked,
        })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          if (response.status) {
            this.errorHandler.stopLoader();
            const content = this.checked ? 'activée' : 'désactivée';
            emitAlertEvent(
              `L'auto-école a été ${content} avec succès.`,
              'success',
              'middle'
            );
            $('#motif-modal').modal('hide');
          }
        });
      this.motif = '';
    }
  }

  cancel() {
    if (this.hasChange) {
      this.action = 'cancel';
      this.inputElement.click();
      this.hasChange = false;
    }
    $('#motif-modal').modal('hide');
    this.action = '';
    this.motif = '';
  }

  switch(activateId: number, event: Event) {
    this.activateId = activateId;
    this.hasChange = true;
    this.inputElement = event.target as HTMLInputElement;
    this.checked = this.inputElement.checked;
    if (!this.inputElement.checked) {
      $('#motif-modal').modal('show');
    } else {
      this.desactive();
    }
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
