import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Examen } from 'src/app/core/interfaces/global';
import { Candidat } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CandidatService } from 'src/app/core/services/candidat.service';
import { ExamenService } from 'src/app/core/services/examen.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SettingService } from 'src/app/core/services/setting.service';
import { redirectTo } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-attestation-success',
  templateUrl: './attestation-success.component.html',
  styleUrls: ['./attestation-success.component.scss'],
})
export class AttestationSuccessComponent {
  currentPage = 'infos-demande';
  pages: any;
  permisId: string | number | null = null;
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
  candidat: Candidat | null = null;
  user: any;
  permisThings: any;
  hasPermis: boolean = false;
  constructor(
    private translate: TranslateService,
    private errorHandler: HttpErrorHandlerService,
    private candidatService: CandidatService,
    private authService: AuthService
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

  selectPermis(): void {
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

  save() {
    this.errorHandler.startLoader();
    this.candidatService
      .demandeAttestation({
        permis_id: this.permisId,
        email: this.email,
      })
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          // this.paiement_success = false;
        })
      )
      .subscribe((response) => {
        this.errorHandler.emitSuccessAlert(response.message);
        redirectTo('/services/suivre-dossier', 3000);
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

  selectPermisSelected(): void {
    this.permisThings = this.categoriespermis.find(
      (permis: any) => permis.id == this.permisId
    );
    return this.permisThings?.categorie_permis?.name;
  }
}
