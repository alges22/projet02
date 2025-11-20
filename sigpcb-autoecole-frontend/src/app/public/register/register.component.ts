import { AfterViewInit, Component } from '@angular/core';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import { Candidat } from 'src/app/core/interfaces/dossier-candidat';
import { OTPEvent, Promoteur } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { emitAlertEvent, hashPhone, redirectTo } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';
type Page = 'register' | 'otp' | 'check-npi' | 'demande';
@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss'],
})
export class RegisterComponent implements AfterViewInit {
  isloading = false;
  showAcceptedMessage: null | boolean = false;
  inputIsValid = false;
  recatchaIsValid = true;
  code: any[] = [];
  npiTab: any[] = [];
  page = 'register' as Page;
  promoteur: Promoteur | null = null;
  codeMessage!: string;
  hasAccount = false;
  form = {
    promoteur_nom: '',
    promoteur_prenoms: '',
    email: '',
    telephone: '',
    num_ifu: '',
    nom_auto_ecole: '',
    password: '',
    password_confirmation: '',
    cpu_accepted: null as null | boolean,
    commune_id: null,
    departement_id: null,
  };

  userServerData: Record<string, any> = {};

  accepted = false;

  signuped = false;
  signuedMessage = '';

  communes: any[] = [];
  isValidOtpCode = false;
  communeSelected: any = null;
  /**
   * 1 - has phone
   * 0 - lost  phone
   * -1 - Unkown phone
   * null - Any action
   */
  hasPhone: 0 | 1 | -1 | null = null;
  phoneHased = '';
  auth = false;
  constructor(
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private storage: StorageService,
    private recaptchaV3Service: ReCaptchaV3Service
  ) {}

  ngOnInit(): void {
    this.recaptchaV3Service
      .execute(environment.recaptcha_key)
      .subscribe((token) => {});

    if (!this.authService.checked()) {
      if (this.storage.has('promoteur') && this.storage.has('demande-page')) {
        this.userServerData = this.storage.get('promoteur') ?? {};
        this.promoteur = this.userServerData as any;
        this.page = this.storage.get('demande-page') ?? 'register';
      }
    } else {
      this.promoteur = this.storage.get('auth');
      this.auth = true;
      this.userServerData = this.promoteur as any;
      this.page = 'demande';
    }
  }

  onNPIChanges(event: OTPEvent) {
    this.npiTab = event.values;
  }

  verify() {
    this.errorHandler.startLoader('Vérification de vos informations');
    this.authService
      .npi({
        npi: this.npiTab.join(''),
      })
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.page = 'check-npi';
        this.userServerData = response.data;
        this.phoneHased = hashPhone(this.userServerData['telephone']);
        this.promoteur = response.data;

        if (response.data['wasPromoteur']) {
          emitAlertEvent(
            `Vous disposez déjà d'un compte. Veuillez vous connecter pour effectuer votre demande.`,
            'danger',
            'middle',
            true
          );
          redirectTo('/connexion', 5000);
        }
        this.errorHandler.stopLoader();
      });
  }

  /**
   * Après quand l'utilisateur clique sur le bouton  recevoir le code
   */
  verifyNPIPhone(event: Event) {
    event.preventDefault();
    this.isloading = true;
    const param = {
      npi: this.userServerData['npi'],
    };
    this.errorHandler.clearServerErrorsMessages('register');
    this.authService
      .sendOtp(param)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.page = 'otp';
        this.isloading = false;
        emitAlertEvent(response.message, 'success');
      });
  }

  /**
   * Après quand l'utilisateur clique sur le bouton  recevoir le code
   */
  signup(event: Event) {
    event.preventDefault();
    this.isloading = true;

    this.errorHandler.clearServerErrorsMessages('register-form');
    this.authService
      .signup(this.form)
      .pipe(
        this.errorHandler.handleServerError('register-form', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.isloading = false;
        this.storage.store('new-registration', {
          email: response.data.email,
        });
        this.signuped = true;
        this.signuedMessage =
          'Un lien de confirmation a été envoyé à votre adresse email. <br> Veuillez-vous connectez-vous à votre adresse email pour valider votre compte';
      });
  }

  onCaptchaResolved(event: string) {
    //this.recatchaIsValid = typeof event === 'string' && event.length > 0;
  }

  onInputValid(event: OTPEvent) {
    if (this.inputIsValid) {
      this.form.num_ifu = event.values.join('');
    }
  }

  onOTPChanges(event: OTPEvent) {
    this.code = event.values;
    if (this.code.length === 6 && event.isValid) {
      this.errorHandler.startLoader('Vérification du code...');
      this.authService
        .verifyPhone({
          npi: this.userServerData['npi'],
          code: this.code.join(''),
        })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.page = 'demande';
          this.storage.store('promoteur', this.userServerData);
          this.storage.store('demande-page', this.page);
          this.errorHandler.stopLoader();
        });
    }
  }

  /**
   * Permet da savoir si le bouton de validation est clickable
   */
  canValidate() {
    return (
      this.recatchaIsValid &&
      this.inputIsValid &&
      this.accepted &&
      !this.isloading
    );
  }

  iAccepted(event: any) {
    this.accepted = event.target.checked;
    if (!this.accepted) {
      this.showAcceptedMessage = true;
    } else {
      this.showAcceptedMessage = false;
    }

    this.form.cpu_accepted = this.accepted;
  }
  ngAfterViewInit(): void {}

  gotoPage(page: Page, event: Event) {
    event.preventDefault();
    this.page = page;
  }

  firstPageValidate() {
    const valid =
      this.npiTab.length == 10 && this.npiTab.every((c) => Number.isInteger(c));
    return this.recatchaIsValid && this.accepted && !this.isloading && valid;
  }
}
