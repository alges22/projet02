import { HttpErrorHandlerService } from './../../core/services/http-error-handler.service';
import {
  AfterViewInit,
  Component,
  ElementRef,
  OnInit,
  ViewChild,
} from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Modal } from 'bootstrap';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import {
  AutoEcole,
  Moniteur,
  OTPEvent,
  Promoteur,
} from 'src/app/core/interfaces/user.interface';
import { AeService } from 'src/app/core/services/ae.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit, AfterViewInit {
  @ViewChild('autoecoleselector')
  aeSlectorModalElement!: ElementRef<HTMLElement>;
  private aeSlectorModal: Modal | null = null;
  isloading = false;
  /**
   * L'auto-école sélectionnée
   */
  selectedAe: number | null = null;
  recatchaIsValid = true;
  npiTab: any[] = [];
  isValidOtpCode = false;
  userType = 'promoteur' as 'promoteur' | 'moniteur';
  page: 'login' | 'otp' | 'forgot-password' = 'login';
  aes: AutoEcole[] = [];
  code = null;
  userServerData: {
    type: 'promoteur' | 'moniteur';
    user_id: string | number;
    moniteur_id: string | number;
    action: string;
  } | null = null;
  private response: any = null;
  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private errorHandler: HttpErrorHandlerService,
    private router: Router,
    private aeService: AeService,
    private recaptchaV3Service: ReCaptchaV3Service
  ) {}

  ngOnInit(): void {
    this.recaptchaV3Service
      .execute(environment.recaptcha_key)
      .subscribe((token) => {});
  }

  /**
   * Envoie le code otp pour moniteur ou pour promoteur
   * @param event
   */
  sendCode(event: Event) {
    this.isloading = true;
    event.preventDefault();
    this.errorHandler.clearServerErrorsMessages('login-form');
    this.authService
      .signin({
        npi: this.npiTab.join(''),
      })
      .pipe(
        this.errorHandler.handleServerError('login-form', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.userServerData = response.data;
        this.userType = this.userServerData?.type ?? 'promoteur';

        this.page = 'otp';
        this.isloading = false;
        emitAlertEvent(response.message, 'success');
      });
  }

  private redirectTo() {
    if (this.userType == 'moniteur') {
      const redirectTo =
        this.route.snapshot.queryParams['returnUrl'] ?? '/gestions/monitoring';
      this.router.navigate([redirectTo]);
      return;
    }
    const r =
      this.route.snapshot.queryParams['returnUrl'] ?? AuthService.REDIRECTTO;
    this.router.navigate([r]);
    return;
  }
  /**
   * Lorsque le code opt est bon
   * @param event
   */
  connect(event: Event) {
    event.preventDefault();
    if (this.userType == 'promoteur') {
      this.logPromoteur();
      return;
    }
    this.loginMoniteur();
  }
  /**
   * Lorsque le NPI change
   * @param event
   */
  onNPIChanges(event: OTPEvent) {
    this.npiTab = event.values;
  }

  /**
   * Renvoie le code opt
   */
  resendCode() {
    this.isloading = true;
    const user_id =
      this.userType == 'promoteur'
        ? this.userServerData?.user_id
        : this.userServerData?.moniteur_id;
    const param = {
      user_id: user_id,
      action: this.userServerData?.action,
    };
    this.authService
      .resendOpt(param)
      .pipe(
        this.errorHandler.handleServerErrors((resp) => {
          this.isloading = false;
        }, 'opt-form')
      )
      .subscribe((resp) => {
        this.errorHandler.emitSuccessAlert(
          'Un code de confirmation vous a été renvoyé à nouveau par Sms'
        );
        this.isloading = false;
      });
  }
  // onCaptchaResolved(event: string) {
  //   this.recatchaIsValid = typeof event === 'string' && event.length > 0;
  // }

  /**
   * Permet da savoir si le bouton de validation est clickable
   */
  canValidate() {
    const valid =
      this.npiTab.length == 10 && this.npiTab.every((c) => Number.isInteger(c));
    return this.recatchaIsValid && valid && !this.isloading;
  }

  otpCodes(event: any) {
    this.isValidOtpCode = event.isValid;
    this.code = event.values.join('');
  }

  ngAfterViewInit(): void {
    if (this.aeSlectorModalElement) {
      this.aeSlectorModal = new Modal(this.aeSlectorModalElement.nativeElement);
    }
  }
  /**
   * Connecte le promoteur
   */
  promoteurLoggin() {
    if (this.selectedAe) {
      const ae = this.aes.find((a) => a.id == this.selectedAe);
      if (ae) {
        const licence = ae.licence;
        this.aeService.select({
          name: ae.name,
          auto_ecole_id: ae.id,
          codeAgrement: ae.agrement?.code,
          codeLicence: !!licence ? licence.code : 'Non disponible',
          endLicence: !!licence ? licence.date_fin : 'Non disponible',
          annexe: {
            name: !!ae.annexe ? ae.annexe.name : 'Innconue',
            phone: !!ae.annexe ? ae.annexe.phone : null,
            email: !!ae.annexe ? ae.annexe.email : null,
          },
        });

        this.aeSlectorModal?.hide();
      }
    }
    if (this.response) {
      const response = this.response;
      if (response) {
        this.authService.attempt(response.data.access_token);
        const user: any = response.data.user as Promoteur;
        user['type'] = 'promoteur';
        this.authService.storageService().store<Promoteur>('auth', user);
        this.isloading = false;
      }
    }
    this.redirectTo();
  }

  private logPromoteur() {
    this.isloading = true;

    const param = {
      user_id: this.userServerData?.user_id,
      code: this.code,
      action: this.userServerData?.action,
    };

    this.errorHandler.startLoader();
    this.authService
      .opt(param)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloading = false;
        }, 'opt-form')
      )
      .subscribe((response) => {
        this.errorHandler.stopLoader();

        this.errorHandler.emitSuccessAlert(response.message);
        this.response = response;
        this.aes = response.data.auto_ecoles;

        if (this.aes.length > 0) {
          if (this.aes.length == 1) {
            this.selectedAe = this.aes[0].id;
            this.promoteurLoggin();
          } else {
            this.aeSlectorModal?.show();
          }
          this.aeService.setAes(this.aes);
        } else {
          this.promoteurLoggin();
        }
        return;
      });
  }
  loginMoniteur() {
    const param = {
      moniteur_id: this.userServerData?.moniteur_id,
      code: this.code,
    };
    this.authService
      .signinMoniteur(param)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloading = false;
        }, 'opt-form')
      )
      .subscribe((response) => {
        this.authService.attempt(response.data.access_token);
        const moniteur: any = response.data.moniteur as Moniteur;
        moniteur['type'] = 'moniteur';
        this.authService.storageService().store<Moniteur>('auth', moniteur);

        const auto_ecole: AutoEcole = response.data.auto_ecole;
        const licence = auto_ecole.licence;
        this.aeService.select({
          auto_ecole_id: auto_ecole.id.toString(),
          endLicence: !!licence ? licence.date_fin : 'Non disponible',
          codeLicence: !!licence ? licence.code : 'Non disponible',
          codeAgrement: auto_ecole.agrement?.code,
          name: auto_ecole.name,
          annexe: {
            name: !!auto_ecole.annexe ? auto_ecole.annexe.name : 'Innconue',
            phone: !!auto_ecole.annexe ? auto_ecole.annexe.phone : null,
            email: !!auto_ecole.annexe ? auto_ecole.annexe.email : null,
          },
        });
        this.isloading = false;
        this.errorHandler.emitSuccessAlert(response.message);
        this.redirectTo();
        return;
      });
  }
}
