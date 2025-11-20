import {
  AfterViewChecked,
  AfterViewInit,
  Component,
  Input,
  OnInit,
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { trim } from 'lodash';

import {
  DemandeAgrement,
  Fiche,
  Promoteur,
  Vehicule,
} from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { CommuneService } from 'src/app/core/services/commune.service';
import { DemandeAgrementService } from 'src/app/core/services/demande-agrement.service';
import { DepartementService } from 'src/app/core/services/departement.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { SettingService } from 'src/app/core/services/setting.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { TransactionService } from 'src/app/core/services/transaction.service';
import { emitAlertEvent, isDigit } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
type Page = 'basic' | 'fiches' | 'monitors' | 'recap' | 'end';

@Component({
  selector: 'app-demande-agrement',
  templateUrl: './demande-agrement.component.html',
  styleUrls: ['./demande-agrement.component.scss'],
})
export class DemandeAgrementComponent
  implements OnInit, AfterViewChecked, AfterViewInit
{
  page = 'basic' as Page;
  @Input() promoteur: Promoteur | null = null;
  vehicules: Vehicule[] = [];
  vehicule = '';
  rejetId: string | null = null;
  moniteurs: Promoteur[] = [];
  moniteurErrors: Promoteur[] = [];
  hasPhone = '';
  fiches: Fiche[] = [
    {
      id: '2',
      label: 'certificat de nationalité du fondé',
      accept: ['.jpg', '.png', 'jpeg'],
      file: null,
      type: 'img',
      required: true,
      name: 'nat_promoteur',
      defaultPath: [],
    },
    {
      id: '3',
      label: 'extrait du casier judiciaire datant de moins de 3 mois du fondé',
      accept: ['.jpg', '.png', 'jpeg'],
      file: null,
      type: 'img',
      required: true,
      name: 'casier_promoteur',
      defaultPath: [],
    },
    {
      id: '4',
      label: 'copie des statuts (pour les sociétés)',
      accept: ['.pdf'],
      file: null,
      type: 'pdf',
      required: false,
      name: 'copie_statut',
      defaultPath: [],
    },
    {
      id: '5',
      label: 'attestation d’inscription au registre de commerce',
      accept: ['.pdf'],
      file: null,
      type: 'pdf',
      required: true,
      name: 'reg_commerce',
      defaultPath: [],
    },
    {
      id: '6',
      label:
        'copie certifiée conforme des titres et autres références professionnelles du fondé',
      accept: ['.pdf'],
      file: null,
      type: 'pdf',
      required: true,
      name: 'ref_promoteur',
      defaultPath: [],
    },
    {
      id: '7',
      label: 'note descriptive des locaux, équipements et matériels à utiliser',
      accept: ['.pdf'],
      file: null,
      type: 'pdf',
      placeholder: 'Chosissez un fichier word ou un pdf',
      required: true,
      name: 'descriptive_locaux',
      defaultPath: [],
    },
    {
      id: '8',
      label:
        'attestation d’inscription au registre des impôts (attestation fiscale valide)',
      accept: ['.pdf'],
      file: null,
      type: 'pdf',
      required: true,
      name: 'attest_fiscale',
      defaultPath: [],
    },
    {
      id: '9',
      label:
        'attestation d’inscription au registre des organismes de la sécurité sociale',
      accept: ['.pdf'],
      file: null,
      type: 'pdf',
      required: true,
      name: 'attest_reg_organismes',
      defaultPath: [],
    },

    {
      id: '12',
      label: 'Carte grise',
      accept: ['.pdf', '.jpg', '.png', 'jpeg'],
      file: null,
      type: 'file',
      required: true,
      name: 'carte_grise',
      multiple: true,
      defaultPath: [],
    },

    {
      id: '13',
      label: 'Assurance visite',
      accept: ['.pdf', '.jpg', '.png', 'jpeg'],
      file: null,
      type: 'file',
      required: true,
      name: 'assurance_visite',
      multiple: true,
      defaultPath: [],
    },
    {
      id: '14',
      label: 'Photos des véhicules',
      accept: ['.jpg', '.png', 'jpeg'],
      file: null,
      type: 'img',
      required: true,
      name: 'photo_vehicules',
      multiple: true,
      defaultPath: [],
    },
  ];

  ficheValides: Fiche[] = [];
  moniteurError: string | null = null;
  form = { commune_id: 0, departement_id: 0, vehicules: [] } as DemandeAgrement;
  departements: any[] = [];
  communes: any[] = [];
  allCommunes: any[] = [];
  ifuTab: number[] = [];
  ifuValid = false;
  formData = new FormData();
  moniteurNpi = '';
  inputIsValid = false;
  auth = false;
  paymentApproved = false;
  demandeId: string | null = null;
  private widget: any = undefined;
  ifuOtp = '';
  paiement: any = null;
  transaction: {
    id: number;
    montant: number;
    phone: string;
    status: string;
    operateur: string;
  } | null = null;
  raisonSociale = '';

  checkoutOptions = {} as any;
  constructor(
    private communeService: CommuneService,
    private departementService: DepartementService,
    private errorHandler: HttpErrorHandlerService,
    private storage: StorageService,
    private authService: AuthService,
    private demandeService: DemandeAgrementService,
    private route: ActivatedRoute,
    private settingService: SettingService
  ) {}

  ngOnInit(): void {
    this._getCommunes();
    this._getDepartements();

    if (this.authService.checked()) {
      this.auth = true;
      this.promoteur = this.storage.get('auth');
      this.form.email_promoteur = this.promoteur?.email || '';
      this.rejetId = this.route.snapshot.paramMap.get('id');

      if (this.rejetId) {
        this._getOldAgrement();
      }
    }

    this.settingService
      .get()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.checkoutOptions = {
          public_key: environment.fedapay_key,
          environment: environment.fedapay_env,
          transaction: {
            amount: response.data.agrement_amount,
            description: "Paiement à l'ANaTT pour agrément d'auto-écoleS",
          },
          onComplete: this.paymentDone.bind(this),
        };
      });
  }
  ngAfterViewInit(): void {}

  onDepartement(target: any): void {
    const depId = Number(target.value);

    if (isNaN(depId) || depId == 0) {
      this.form.departement_id = 0;
      this.communes = this.allCommunes;
    } else {
      this.form.departement_id = depId;
      this.communes = this.allCommunes.filter((commune) => {
        return commune.departement_id == depId;
      });
    }

    this.insertInTemp();
  }

  onCommune(target: any): void {
    const communeId = Number(target.value);

    if (isNaN(communeId) || communeId == 0) {
      this.form.commune_id = 0;
    } else {
      this.form.commune_id = communeId;

      const commune = this.allCommunes.find((c) => c.id == communeId);
      if (commune) {
        const dep = this.departements.find((dep) => {
          return dep.id == commune.departement_id;
        });

        if (dep) {
          this.form.departement_id = dep.id;
        }
      }
    }
  }

  /**
   * Aller à une page donnée
   * @param page
   */
  go(page: Page) {
    if (page == 'monitors') {
      this.verifIfu();
    } else {
      this.page = page;
    }
  }

  private _getDepartements() {
    this.errorHandler.startLoader();
    this.departementService.getDepartements().subscribe((response) => {
      this.departements = response.data;
      this.errorHandler.stopLoader();
    });
  }

  private _getCommunes() {
    this.errorHandler.startLoader();
    this.communeService.getCommunes().subscribe((response) => {
      this.allCommunes = response.data;
      this.allCommunes = this.allCommunes.map((item) => {
        item.name = `${item.name} / ${item.departement.name}`;
        return item;
      });

      this.communes = this.allCommunes;
      this.errorHandler.stopLoader();
    });
  }
  ngAfterViewChecked(): void {}
  private insertInTemp() {
    /*  this.storage.store('demande_agrement', this.form);
    if (this.moniteurs.length > 0) {
      this.storage.store('demande_moniteurs', this.moniteurs);
    } */
  }
  addMoniteur(pressed = false) {
    this.moniteurErrors = [];
    const npis = this.moniteurNpi.split('');
    if (npis.some((i) => !isDigit(i))) {
      this.moniteurError = 'Le numéro NPI est incorrect';
      return;
    } else {
      this.moniteurError = null;
    }

    if (pressed && this.moniteurNpi.length != 10) {
      this.moniteurError = 'Le numéro NPI est trop court';
    }

    if (this.moniteurNpi.length == 10) {
      const searched = this.moniteurs.find(
        (moniteur) => moniteur.npi == this.moniteurNpi
      );
      if (!searched) {
        this.errorHandler.startLoader('Vérification du NPI ...');
        this.authService
          .npi({
            npi: this.moniteurNpi,
          })
          .pipe(this.errorHandler.handleServerErrors())
          .subscribe((response) => {
            if (!searched) {
              if (!response.data.wasMoniteur) {
                this.moniteurErrors.push({
                  prenoms: response.data.prenoms,
                  nom: response.data.nom,
                } as any);
              } else {
                this.moniteurs.push({
                  prenoms: response.data.prenoms,
                  nom: response.data.nom,
                  npi: response.data.npi,
                  telephone: response.data.telephone,
                } as any);
              }
            }

            this.moniteurNpi = '';
            this.errorHandler.stopLoader();
          });
      } else {
        return;
      }
    }
  }

  onKeydown(event: any) {
    if (event.keyCode === 13) {
      this.addMoniteur();
    }
  }
  removeMoniteur(npi: string) {
    this.moniteurs = this.moniteurs.filter((moniteur) => moniteur.npi != npi);
  }

  onFile(file: File | undefined | File[], index: number) {
    if (Array.isArray(file)) {
      this.fiches[index].file = file;
    } else {
      this.fiches[index].file = file;
      if (!file) {
        this.fiches[index].defaultPath = [];
      }
    }
    this.ficheValides = this.fiches.filter((fiche) => !!file);
  }
  basicFormValid() {
    const autoEcoleValid =
      !!this.form.auto_ecole && this.form.auto_ecole.length > 1;

    const ifuValid = !!this.form.ifu && !!this.form.ifu.length;

    const communeIdValid = this.form.commune_id != 0;
    const departementIdValid = this.form.departement_id != 0;
    return autoEcoleValid && ifuValid && communeIdValid && departementIdValid;
  }

  fichesValides() {
    return this.fiches.every((fc) => {
      if (fc.required) {
        return !!fc.file || !!fc.defaultPath.length;
      }

      return true;
    });
  }

  private __prepareData() {
    const form: any = this.form;
    const formData = new FormData();
    for (const name in form) {
      if (Object.prototype.hasOwnProperty.call(form, name)) {
        const value = form[name];
        if (!!value) {
          formData.append(name, trim(value));
        }
      }
    }

    for (const m of this.moniteurs) {
      formData.append('moniteurs[]', m.npi);
    }

    for (const fiche of this.ficheValides) {
      if (!!fiche.file) {
        if (fiche.multiple) {
          const files = fiche.file;
          if (Array.isArray(files)) {
            for (const binary of files) {
              formData.append(`${fiche.name}[]`, binary);
            }
          }
        } else {
          formData.append(fiche.name, fiche.file as any);
        }
      }
    }
    for (const v of this.vehicules) {
      formData.append('vehicules[]', v.immatriculation);
    }

    formData.append('npi', this.promoteur?.npi || '');

    if (this.rejetId) {
      formData.append('demande_rejet_id', this.rejetId);
    }

    this.formData = formData;
  }
  removeVehicule(i: string) {
    this.vehicules = this.vehicules.filter((v) => v.immatriculation != i);
  }
  addVehicule() {
    const searched = this.vehicules.find(
      (v) => v.immatriculation == this.vehicule
    );
    if (!searched) {
      this.vehicules.push({
        immatriculation: this.vehicule,
      } as Vehicule);
    }

    this.vehicule = '';
  }
  save() {
    this.__prepareData();

    /**
     * Mise à jour lors qu'un rejet a été soumis
     */
    if (this.rejetId) {
      this.formData.append('demande_rejet_id', this.rejetId);
      this.errorHandler.startLoader(
        "L'envoie de la demande est en cours, cela pourrait prendre quelques minutes ..."
      );
      this.demandeService
        .update(this.formData, this.rejetId)
        .pipe(this.errorHandler.handleServerErrors((response) => {}))
        .subscribe((response) => {
          emitAlertEvent(response.message, 'success');
          this.page = 'end';
          this.storage.remove('demande-page');
          this.storage.remove('promoteur');
          this.errorHandler.stopLoader();
        });
    } else {
      this.errorHandler.startLoader(
        'Préparation du paiement en cours, cela pourrait prendre quelque minutes, veuillez patienter svp!'
      );
      /**
       * Enregistre la demande temporaiementn
       */
      this.demandeService
        .demande(this.formData)
        .pipe(this.errorHandler.handleServerErrors((response) => {}))
        .subscribe((response) => {
          this.errorHandler.stopLoader();
          this.demandeId = response.data.id;
          // @ts-ignore
          const fedpay = window['FedaPay'];
          if (fedpay) {
            fedpay.init(this.checkoutOptions).open();
          }
        });
    }
  }

  private _getOldAgrement() {
    if (this.rejetId) {
      this.errorHandler.startLoader();
      this.demandeService
        .rejets(this.rejetId)
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          const data = response.data;

          this.form = data;
          this.moniteurs = data.monitors;
          this.ifuTab = this.form.ifu.split('').map((i) => Number(i));
          const fiche: Record<string, string | undefined> = data.fiche;
          for (const f of this.fiches) {
            if (f.name in fiche) {
              let defaultPath = fiche[f.name];
              if (Array.isArray(defaultPath)) {
                f.defaultPath = defaultPath;
              } else {
                f.defaultPath = [defaultPath ?? ''];
              }
            }
          }

          this.ficheValides = this.fiches.filter((f) => !!f.defaultPath);
          let vehicules = [];
          try {
            vehicules = JSON.parse(data.vehicules);
          } catch (error) {}
          this.vehicules = vehicules;
          this.errorHandler.stopLoader();
        });
    }
  }

  private submit(form: FormData) {
    this.errorHandler.startLoader(
      "L'envoie de la demande est en cours, cela pourrait prendre quelques minutes ..."
    );
    this.demandeService
      .submit(form)
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.page = 'end';
        emitAlertEvent(response.message, 'success');
        this.storage.remove('demande-page');
        this.storage.remove('promoteur');
        this.errorHandler.stopLoader();
      });
  }

  paymentDone(response: any) {
    const transaction = {
      id: response.transaction.id,
      montant: response.transaction.amount,
      phone: response.transaction.payment_method.number,
      status: response.transaction.status,
      operateur: response.transaction.mode,
    };

    if (this.paymentApproved) {
      const formData = new FormData();
      formData.append('transaction', JSON.stringify(this.transaction));
      formData.append('demande_id', trim(this.demandeId || ''));
      this.submit(formData);
    } else {
      if (response.reason !== 'DIALOG DISMISSED') {
        if (transaction.status == 'approved') {
          this.transaction = transaction;
          const formData = new FormData();
          formData.append('transaction', JSON.stringify(transaction));
          formData.append('demande_id', trim(this.demandeId || ''));
          this.submit(formData);
        } else {
          emitAlertEvent(
            "L'envoie de votre demande a échoué, le paiement n'a pu être effectué"
          );
        }
      }
    }
  }

  verifIfu() {
    if (this.ifuValid) {
      this.page = 'monitors';
      return;
    }
    this.raisonSociale = '';
    this.errorHandler.startLoader('Chargement ...');
    this.authService
      .checkifu(this.form.ifu, {
        npi: this.promoteur?.npi,
      })
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.page = 'basic';
        })
      )
      .subscribe((response) => {
        this.hasPhone = response.data.phone;
        this.raisonSociale = response.data.raisonSocial;
        //this.ifuOtpModal('show');
        this.continueOnMonitors();
        this.errorHandler.stopLoader();
      });
  }

  onTapeIfu() {
    this.ifuValid = false;
  }
  ifuOtpModal(action: 'hide' | 'show') {
    $('#ifu-otp-modal').modal(action);
  }

  onOkIfuOtp() {
    if (this.isValidIfuOtp()) {
      this.continueOnMonitors();
    }
  }
  private continueOnMonitors() {
    this.ifuValid = true;
    this.page = 'monitors';
    this.ifuOtp = '';
    // this.authService
    //   .verifyIfu({
    //     ifu: this.form.ifu,
    //     npi: this.promoteur?.npi,
    //     code: this.ifuOtp,
    //   })
    //   .pipe(
    //     this.errorHandler.handleServerErrors((response) => {
    //       this.ifuValid = false;
    //     })
    //   )
    //   .subscribe((response) => {
    //     this.ifuValid = true;
    //     this.page = 'monitors';
    //     this.ifuOtp = '';
    //     emitAlertEvent(response.message, 'success');
    //     this.ifuOtpModal('hide');
    //   });
  }

  isValidIfuOtp() {
    if (!this.ifuOtp) {
      return false;
    }
    const otps = String(this.ifuOtp).split('');
    if (otps.length != 6) {
      return false;
    }
    const integers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];

    return otps.every((i) => integers.includes(i));
  }
  resendIfuCode() {
    this.errorHandler.startLoader();
    this.authService
      .resendIfuCode({
        ifu: this.form.ifu,
        npi: this.promoteur?.npi,
      })
      .pipe(this.errorHandler.handleServerErrors((response) => {}))
      .subscribe((response) => {
        this.errorHandler.stopLoader();
        emitAlertEvent(response.message, 'success');
      });
  }
}
