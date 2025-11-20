import { AfterViewInit, Component, Input, OnInit } from '@angular/core';
import {
  AuthenticitePermis,
  DuplicataRemplacement,
} from 'src/app/core/interfaces/services';
import { AnnexeAnattService } from 'src/app/core/services/annexe-anatt.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { DuplicataRemplacementService } from 'src/app/core/services/duplicata-remplacement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SettingService } from 'src/app/core/services/setting.service';
import {
  redirectTo,
  toFormData,
  utcNow,
  emitAlertEvent,
} from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
import { TrivuleForm } from 'trivule';

@Component({
  selector: 'app-duplicata-permis',
  templateUrl: './duplicata-permis.component.html',
  styleUrls: ['./duplicata-permis.component.scss'],
})
export class DuplicataPermisComponent implements AfterViewInit, OnInit {
  page = 1;
  pages = [1];
  imageSrc = '';
  form = { annexe_id: 0, type: '', group_sanguin: '' } as DuplicataRemplacement;
  formValid = false;
  copiePermis: File | null = null;
  fileValid = false;
  fileRules = [
    {
      rule: 'required',
      message: 'Le fichier est requis',
    },
    {
      rule: 'mimes:image/*',
      message: 'Veuillez sélectionner une image (JPG, JPEG ou PNG)',
    },
    {
      rule: 'size:1MB',
      message: 'Le fichier ne doit pas dépasser 1Mo',
    },
  ];
  candidat: {
    nom: '';
    npi: '';
    prenoms: '';
  } | null = null;
  checkoutButtonOptions = {} as any;
  download_url: string | null = null;
  groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
  annexe = '';
  paymentApproved = false;
  transaction: {
    transactionId: number;
    montant: number;
    phone: string;
    status: string;
    operateur: string;
    duplicata_id: number;
    date_payment: string;
  } | null = null;
  duplicataId: number | null = null;
  annexes: any[] = [];
  @Input('rejetId') rejetId: string | null = null;
  private trivuleForm: TrivuleForm | null = null;
  constructor(
    private errorHandler: HttpErrorHandlerService,
    private duplicataService: DuplicataRemplacementService,
    private authService: AuthService,
    private annexeanattService: AnnexeAnattService,

    private settingService: SettingService
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
      redirectTo('/connexion');
    }

    this.settingService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.checkoutButtonOptions = {
          public_key: environment.fedapay.key,
          environment: environment.fedapay.sandbox,
          transaction: {
            amount: response.data.duplicata_amount,
            description: "Paiement à l'ANaTT du Service ",
          },
          currency: {
            iso: 'XOF',
          },
          onComplete: this.paymentDone.bind(this),
        };
      });

    this._getAnnexes();
    this.oldDemande();
  }
  pageActive(page: number) {
    return this.pages.includes(page);
  }

  ngAfterViewInit(): void {
    if (!this.trivuleForm) {
      this.trivuleForm = new TrivuleForm('#duplicata-permis-form', {
        feedbackSelector: '.text-feedback',
      });
    }

    this.trivuleForm.make({
      type: {
        rules: 'required',
        messages: 'Ce champ est requis',
      },
      num_permis: {
        rules: 'required|regex:^[A-Z0-9]+$|minlength:8|maxlength:25',
        messages: {
          required: 'Ce champ est obligatoire',
          minlength: 'Le numéro du permis semble être trop court',
          maxlength: 'Le numéro du permis semble être trop long',
          regex: "Le format du numéro du permis n'est pas pris en charge",
        },
      },
      phone: {
        rules: 'required|regex:^[0-9+]+$|minlength:8|maxlength:21',
        messages: {
          required: 'Ce champ est obligatoire',
          minlength: 'Numéro de téléphone trop court',
          maxlength: 'Le numéro de téléphone est trop longue',
          regex: "Le format du numéro n'est pas pris en charge",
        },
      },
    });
    this.trivuleForm.onPasses(() => {
      this.formValid = true;
    });

    this.trivuleForm.onFails(() => {
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
    if (this.copiePermis || !!this.form.file) {
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

  startPaiement() {
    // @ts-ignore
    const FedaPay = window['FedaPay'];
    if (FedaPay) {
      FedaPay.init(this.checkoutButtonOptions).open();
    }
  }

  paymentDone(response: any) {
    if (this.duplicataId) {
      const data = {
        transactionId: response.transaction.id,
        montant: response.transaction.amount,
        phone: response.transaction.payment_method.number,
        status: response.transaction.status,
        operateur: response.transaction.mode,
        payment_for: 'demande-duplicata',
        duplicata_id: this.duplicataId,
        date_payment: '2023-02-02',
      };

      if (this.paymentApproved) {
        const formData = toFormData(data);
        this.submit(formData);
      } else {
        if (response.reason !== 'DIALOG DISMISSED') {
          if (data.status == 'approved') {
            this.transaction = data;
            this.paymentApproved = true;
            this.authService.storageService().store('dtrt', data);
            const formData = toFormData(data);
            this.submit(formData);
          } else {
            emitAlertEvent(
              "L'envoie de votre demande a échoué, le paiement n'a pu être effectué"
            );
          }
        }
      }
    }
  }

  private submit(form: FormData) {
    this.errorHandler.startLoader();
    this.duplicataService
      .submit(form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.page = 3;
        this.pages.push(this.page);
        this.retriveUrl(response.message);
        this.authService.storageService().remove('dtrt');
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  private _getAnnexes() {
    this.errorHandler.startLoader();
    this.annexeanattService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        if (response.status) {
          this.annexes = response.data;
          this.errorHandler.stopLoader();
        }
      });
  }

  selectAnnexe(target: any) {
    const annexeId = target.value;
    const annexe = this.annexes.find((a) => a.id == annexeId);

    if (annexe) {
      this.form.annexe_id = annexe.id;
      this.annexe = annexe.name;
    }
  }

  private retriveUrl(message: string) {
    const regex = /<a[^>]+href=['"]([^'"]+)['"][^>]*>ici<\/a>/;
    const match = message.match(regex);

    if (match) {
      const url = match[1];
      this.download_url = url;
    } else {
      this.download_url = null;
    }
  }

  private oldDemande() {
    if (this.rejetId) {
      this.duplicataService
        .find(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.form = response.data;
          this.fileValid = true;
          this.formValid = true;
          this.imageSrc = this.asset(this.form.file);
        });
    }
  }

  asset(path: string) {
    return environment.endpoints.asset + path;
  }

  private _save() {
    if (!this.paymentApproved) {
      const data = toFormData(this.form, [
        {
          name: 'file',
          value: this.copiePermis,
        },
      ]);
      this.errorHandler.startLoader();
      this.duplicataService
        .post(data)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          const data = response.data as AuthenticitePermis;
          this.duplicataId = data.id;
          this.errorHandler.stopLoader();
          this.startPaiement();
        });
    } else {
      const data = toFormData(this.transaction, [
        {
          name: 'file',
          value: this.copiePermis,
        },
      ]);
      this.submit(data);
    }
  }

  private _update() {
    const fileData = [
      {
        name: 'file',
        value: this.copiePermis,
      },
    ];
    const form = this.form as any;

    delete form.file;

    this.form = form;

    const data = toFormData(this.form, !!this.copiePermis ? fileData : []);
    data.append('rejet_id', this.rejetId || '');
    this.errorHandler.startLoader();
    this.duplicataService
      .update(data)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        const data = response.data;
        this.duplicataId = data.id;
        this.errorHandler.stopLoader();
        this.page = 3;
        this.pages.push(this.page);
      });
  }
  validateFile(status: boolean) {
    this.fileValid = status;
  }
}
