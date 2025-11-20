import { Component, OnInit } from '@angular/core';
import { Affiliation } from 'src/app/core/interfaces';
import { AutoEcole } from 'src/app/core/interfaces/user.interface';
import { AeService } from 'src/app/core/services/ae.service';
import { AffiliationService } from 'src/app/core/services/affiliation.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-affiliation',
  templateUrl: './affiliation.component.html',
  styleUrls: ['./affiliation.component.scss'],
})
export class AffiliationComponent implements OnInit {
  auth = null;
  paginateData: { data: any[]; total: number } = {} as any;
  candidats: any[] = [];
  form = {} as Affiliation;
  candidatToAdd: any = null;
  autoEcole: AutoEcole | null = null;
  aes: AutoEcole[] = [];
  candidatToDelete: any = null;
  filters = {
    status: null,
    auto_ecole_id: null as any,
    npi: null,
    page: 1,
  };
  constructor(
    private storage: StorageService,
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private aeService: AeService,
    private affiliationService: AffiliationService
  ) {}
  ngOnInit(): void {
    this.auth = this.storage.get('auth');
    this.getAes();
  }
  post() {
    this.affiliationService
      .post(this.form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.candidatToAdd = null;
        const oldAeId = this.form.auto_ecole_id;
        this.form = {} as Affiliation;
        this.form.auto_ecole_id = oldAeId;
        this.get();
        this.affiliationModal('hide');
        emitAlertEvent(response.message, 'success');
      });
  }
  get() {
    this.errorHandler.startLoader();
    this.affiliationService
      .get(this.filters)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.paginateData = response.data;
        this.candidats = this.paginateData.data;

        this.errorHandler.stopLoader();
      });
  }

  isValid() {
    return String(this.form.npi).length >= 10 && !!this.form.auto_ecole_id;
  }
  resilier() {}
  paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.filters.page,
      totalItems: this.paginateData?.total ?? 0,
    };
  }
  paginate(number: number) {
    this.filters.page = number ?? 1;
    this.get();
  }

  affiliationModal(action: 'show' | 'hide' = 'show') {
    $('#affiliation-modal').modal(action);
  }
  beforeAdd() {
    this.authService
      .npi({ npi: this.form.npi })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.candidatToAdd = response.data;
        this.affiliationModal('show');
      });
  }
  onAeChanges(target: any) {
    this.autoEcole = this.aes.find((a) => a.id == target.value) ?? null;
    if (this.autoEcole) {
      this.filters.auto_ecole_id = this.autoEcole.id;
    }
    this.get();
  }

  private getAes() {
    this.aeService.getAes().subscribe((aes) => {
      this.aes = aes;
      this.aes = this.aes.filter((ae) => ae.status);
      const ae = this.aeService.getAe();
      if (ae) {
        this.form.auto_ecole_id = (ae.auto_ecole_id ?? 0) as any;
        this.filters.auto_ecole_id = this.form.auto_ecole_id ?? 0;
        this.onAeChanges({ value: this.form.auto_ecole_id });
        this.get();
      }
    });
  }
}
