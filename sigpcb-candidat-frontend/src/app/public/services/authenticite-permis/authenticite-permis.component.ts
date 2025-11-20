import {
  AfterViewInit,
  Component,
  Input,
  OnDestroy,
  OnInit,
} from '@angular/core';
import { data } from 'jquery';
import { AuthenticitePermis } from 'src/app/core/interfaces/services';
import { TransactionResponse } from 'src/app/core/interfaces/transaction';
import { Candidat } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { AuthenticiteDuPermisService } from 'src/app/core/services/authenticite-du-permis.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { TransactionService } from 'src/app/core/services/transaction.service';
import {
  emitAlertEvent,
  redirectTo,
  toFormData,
} from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-authenticite-permis',
  templateUrl: './authenticite-permis.component.html',
  styleUrls: ['./authenticite-permis.component.scss'],
})
export class AuthenticitePermisComponent implements AfterViewInit, OnInit {
  page = 1;
  pages = [1];
  imageSrc = '';
  form = {} as AuthenticitePermis;
  formValid = false;
  copiePermis: File | null = null;
  qvForm: any = null;
  fileValid = false;
  candidat: Candidat | null = null;
  download_url: string | null = null;
  @Input('rejetId') rejetId: string | null = null;
  transaction: {
    amount: number;
    date_payment: string;
  } | null = null;
  payment: {
    id: string | number;
    uuid: string;
  } | null = null;
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private authenticiteService: AuthenticiteDuPermisService,
    private authService: AuthService
  ) {}
  ngOnInit(): void {
    if (!this.authService.checked()) {
      redirectTo('/connexion');
    }

    const auth = this.authService.auth();
    if (typeof auth === 'object' && !!auth) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: auth.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.candidat = response.data;
          this.errorHandler.stopLoader();
        });
    } else {
      if (!this.authService.checked()) {
        redirectTo('/connexion');
      }
    }

    this.oldDemande();
  }
  pageActive(page: number) {
    return this.pages.includes(page);
  }

  ngAfterViewInit(): void {
    if (!this.qvForm) {
      //@ts-ignore
      this.qvForm = new QvForm('#auth-permis-form');
      this.qvForm.init();
    }

    this.qvForm.onPasses(() => {
      this.formValid = true;
    });

    this.qvForm.onFails(() => {
      this.formValid = false;
    });
  }
  onFilePermisPrealableChange(file: File | undefined) {
    if (file) {
      this.copiePermis = file;
      this.imageSrc = URL.createObjectURL(this.copiePermis);
      this.fileValid = true;
    } else {
      this.copiePermis = null;
      this.fileValid = false;
    }
  }
  gotoPage(page: number) {
    this.page = page;
    for (let index = 1; index <= this.page; index++) {
      if (!this.pages.includes(index)) {
        this.pages.push(index);
      }
    }
  }

  openImageModal() {
    if (this.imageSrc) {
      $(`#openImageModal`).modal('show');
    }
  }

  save() {
    if (this.rejetId) {
      this._update();
    } else {
      this._save();
    }
  }

  private retriveUrl(url: string) {
    if (url) {
      this.download_url = url;
    } else {
      this.download_url = null;
    }
  }

  private oldDemande() {
    if (this.rejetId) {
      this.authenticiteService
        .find(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.form = response.data;
          this.fileValid = true;
          this.formValid = true;
          this.imageSrc = this.asset(this.form.permis_file);
        });
    }
  }

  asset(path: string) {
    return environment.endpoints.asset + path;
  }

  private _update() {
    const fileData = [
      {
        name: 'permis_file',
        value: this.copiePermis,
      },
    ];
    const form = this.form as any;

    delete form.permis_file;

    this.form = form;

    const data = toFormData(this.form, !!this.copiePermis ? fileData : []);
    data.append('rejet_id', this.rejetId || '');
    this.errorHandler.startLoader();
    this.authenticiteService
      .update(data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        this.page = 3;
        this.pages.push(this.page);
      });
  }

  private _save() {
    const fileData = [
      {
        name: 'permis_file',
        value: this.copiePermis,
      },
    ];
    const data = toFormData(this.form, !!this.copiePermis ? fileData : []);
    this.errorHandler.startLoader();
    this.authenticiteService
      .post(data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.payment = {
          id: response.data.transactionId,
          uuid: response.data.uuid,
        };
        this.errorHandler.stopLoader();
      });
  }
  getTransaction(data: TransactionResponse) {
    if (data.status == 'approved') {
      this.transaction = data;
      this.gotoPage(3);
      this.retriveUrl(data.url);
    } else {
      emitAlertEvent(
        "L'envoie de votre demande a échoué, le paiement n'a pu être effectué"
      );
    }
  }
}
