import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { SettingService } from 'src/app/core/services/setting.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-permis-numerique',
  templateUrl: './permis-numerique.component.html',
  styleUrls: ['./permis-numerique.component.scss'],
})
export class PermisNumeriqueComponent {
  currentPage = 'infos-demande';
  pages: any;
  categorypermis: any;
  categoriespermis: any;
  montant_payer: any;
  phone_payment: any;
  date_payment: any;
  download_url: any;
  messageDownloadPermis: any;
  permis: any;
  pagesTemps = [
    {
      name: 'Infos de la demande',
      active: true,
      page: 'infos-demande',
    },
    {
      name: 'Summary',
      active: false,
      page: 'recapitulatif',
    },
    {
      name: 'Paiement',
      active: false,
      page: 'paiement',
    },
  ];
  isValidInfosDemande = false;
  quickvInfosDemande: any;
  email: any;
  permisSelected: any;
  candidat: any;
  user: any;
  permisThings: any;
  checkoutButtonOptions = {} as any;
  hasPermis: boolean = false;
  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private authService: AuthService,

    private settingService: SettingService
  ) {}
  ngOnInit(): void {
    this.user = this.authService.storageService().get('auth');
    this._getCandidatWithNpi();
    // Traduire les prestationsTemp initialement
    this.translatePages();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePages();
    });

    this._getUserPermis();

    this.settingService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.checkoutButtonOptions = {
          public_key: environment.fedapay.key,
          environment: environment.fedapay.sandbox,
          transaction: {
            amount: response.data.permis_num_amount,
            description: "Paiement à l'ANaTT du Service ",
          },
          currency: {
            iso: 'XOF',
          },
          onComplete: this.onCheckoutComplete.bind(this),
        };
      });
  }

  ngAfterViewInit(): void {
    //@ts-ignore
    this.quickvInfosDemande = new QvForm('#infos-demande');
    this.quickvInfosDemande.init();

    this.quickvInfosDemande.onValidate((qvForm: any) => {
      this.isValidInfosDemande = qvForm.passes();
    });
  }

  private _getUserPermis() {
    this.candidatService
      .getUserPermis()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.categoriespermis = response.data;
        this.errorHandler.stopLoader();
      });
  }

  translatePages(): void {
    const translationPromises = this.pagesTemps.map((page: any) => {
      return this.translate
        .get(page.name)
        .toPromise()
        .then((translation: string) => {
          return { ...page, name: translation };
        });
    });

    Promise.all(translationPromises).then((translatedPrestations: any[]) => {
      // Mettre à jour les prestationsTemp traduites
      this.pages = translatedPrestations;
    });
  }

  private _getCandidatWithNpi() {
    if (this.user) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: this.user.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.candidat = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  selectPermis(categorypermisId: any): void {
    this.errorHandler.startLoader();

    this.candidatService
      .getUserPermis()
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          // this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        if (response.status) {
          if (response.statuscode == 200) {
            this.hasPermis = true;
            this.permis = response.data;
          } else if (response.statuscode == 404) {
            this.errorHandler.emitAlert(
              response.message,
              'danger',
              'middle',
              true
            );
            this.hasPermis = false;
          }
        }
      });
  }

  onCheckoutComplete(resp: any) {
    // @ts-ignore
    const FedaPay = window['FedaPay'];
    if (resp.reason !== 'DIALOG DISMISSED') {
      if (resp.transaction.status === 'approved') {
        const data = {
          email: this.email,
          code_permis: this.permisThings.code_permis,
          categorie_permis_id: this.categorypermis,
          agregateur: 'fedapay',
          description: resp.transaction.description,
          transaction_id: resp.transaction.id,
          reference: resp.transaction.reference,
          mode: resp.transaction.mode,
          operation: resp.transaction.operation,
          transaction_key: resp.transaction.transaction_key,
          montant: resp.transaction.amount,
          phone_payment: resp.transaction.payment_method.number,
          ref_operateur: resp.transaction.transaction_key,
          moyen_payment: 'momo',
          status: resp.transaction.status,
          date_payment: resp.transaction.payment_method.created_at,
        };
        this._savePaiement(data);
        // this.paiement_success = true;
        this.montant_payer = resp.transaction.amount;
        this.phone_payment = resp.transaction.payment_method.number;
        this.date_payment = resp.transaction.payment_method.created_at;
      }
    }
  }

  private _savePaiement(data: any, event?: Event) {
    this.errorHandler.startLoader();
    this.candidatService
      .savePaimentPermisNumerique(data)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          // this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.download_url = response.data.url;
        this.messageDownloadPermis = response.message;
        this.errorHandler.stopLoader();
        this.currentPage = 'paiement';
        this.gotoPage('paiement', event);
      });
  }

  gotoPage(page: string, event?: Event) {
    event?.preventDefault();
    this.currentPage = page;
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  infosDemandeIsValid() {
    return this.isValidInfosDemande && this.hasPermis;
  }

  setActivePage(page: string) {
    for (let i = 0; i < this.pages.length; i++) {
      if (this.pages[i].page === page) {
        this.pages[i].active = true;
      }
    }
  }

  selectPermisSelected(permisId: any): void {
    this.permisThings = this.categoriespermis.find(
      (permis: any) => permis.categorie_permis.id == permisId
    );
    return this.permisThings?.categorie_permis?.name;
  }

  payment() {
    // @ts-ignore
    const FedaPay = window['FedaPay'];
    if (FedaPay) {
      FedaPay.init(this.checkoutButtonOptions).open();
    }
  }
}
