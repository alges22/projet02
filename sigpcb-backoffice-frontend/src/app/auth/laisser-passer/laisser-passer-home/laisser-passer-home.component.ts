import { Component } from '@angular/core';
import { DispensePaiement } from 'src/app/core/interfaces';
import { PermissionExpression } from 'src/app/core/interfaces/role';
import { DispensePaymentService } from 'src/app/core/services/dispense-payment.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ImageService } from 'src/app/core/services/image.service';
import { UserAccessService } from 'src/app/core/services/user-access.service';
import { UsersService } from 'src/app/core/services/users.service';
import { CounterService } from 'src/app/core/services/counter.service';

@Component({
  selector: 'app-laisser-passer-home',
  templateUrl: './laisser-passer-home.component.html',
  styleUrls: ['./laisser-passer-home.component.scss'],
})
export class LaisserPasserHomeComponent {
  dispenses: DispensePaiement[] = [];
  npiInfo: any = null;
  addModal = false;
  currentDispense: DispensePaiement | null = null;
  currentAction: string | null = null;
  searchNpi = '';
  total = 0;
  currentPage = 1;
  constructor(
    private readonly usersService: UsersService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly imageService: ImageService,
    private readonly dispensePaymentService: DispensePaymentService,
    private readonly userAccessService: UserAccessService
  ) {}

  npi = '';
  info: any;
  onValidate(payment: DispensePaiement, action: 'validate' | 'reject'): void {
    this.errorHandler.startLoader();
    this.dispensePaymentService
      .action({
        action: action,
        id: payment.id,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        this.get();
      });
  }
  addDispenseModal() {
    this.currentDispense = null;
    this.addModal = true;
    this.npi = '';
    this.searchNpi = '';
    this.info = null;
  }

  ngOnInit() {
    this.get();
  }
  get() {
    this.errorHandler.startLoader();
    this.dispensePaymentService
      .get({
        search: this.searchNpi,
        page: this.currentPage,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.dispenses = response.data.data;
        this.total = response.data.total;
        this.errorHandler.stopLoader();
      });
  }
  userCanAdd() {
    return this.userAccessService.hasOneOf('all', 'edit-dispense-paiement');
  }

  userCanValidate() {
    return this.userAccessService.hasOneOf('all', 'manage-dispense-paiement');
  }
  addDispense() {
    this.errorHandler.startLoader();
    this.dispensePaymentService
      .post({
        npi: this.npi,
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.get();
        this.addModal = false;
        this.npi = '';
        this.info = null;
      });
  }

  verifyCandidat() {
    if (this.npi.length < 10) {
      return;
    }
    this.errorHandler.startLoader();
    this.usersService
      .npiInfos(this.npi)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.info = response.data;
        this.errorHandler.stopLoader();

        this.imageService
          .getImages({
            npis: [this.npi],
          })
          .subscribe((response) => {
            console.log(response);
          });
      });
  }

  getBadge(status: string) {
    const statues: Record<string, string> = {
      init: 'Nouvelle',
      used: 'Utilisée',
      validated: 'Validée',
      rejected: 'Rejetée',
    };

    return statues[status] || 'N/A';
  }

  openDispenseModal(dispense: DispensePaiement, action: 'validate' | 'reject') {
    this.currentDispense = dispense;
    this.currentAction = action;
  }

  paginate(page: number) {
    this.currentPage = page ?? 1;
    this.get();
  }
  get paginateArgs() {
    return {
      itemsPerPage: 10,
      currentPage: this.currentPage,
      totalItems: this.total,
    };
  }
}
