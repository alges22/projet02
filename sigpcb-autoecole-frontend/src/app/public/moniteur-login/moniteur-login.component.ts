import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import {
  Ae,
  Moniteur,
  OTPEvent,
  AutoEcole,
} from 'src/app/core/interfaces/user.interface';
import { AeService } from 'src/app/core/services/ae.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-moniteur-login',
  templateUrl: './moniteur-login.component.html',
  styleUrls: ['./moniteur-login.component.scss'],
})
export class MoniteurLoginComponent {
  isloading = false;

  recatchaIsValid = true;
  npiTab: any[] = [];
  isValidOtpCode = false;

  page: 'login' | 'otp' | 'forgot-password' = 'login';

  code = null;
  moniteurData: { npi: string; auto_ecole_id: number; id: number } | null =
    null;

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

  verify(event: Event) {
    this.isloading = true;
    event.preventDefault();
    this.errorHandler.clearServerErrorsMessages('login-form');
    this.errorHandler.startLoader('Vérification de vos informations !');
    this.authService
      .verifyMoniteur({
        npi: this.npiTab.join(''),
      })
      .pipe(
        this.errorHandler.handleServerError('login-form', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.moniteurData = response.data;
        this.page = 'otp';
        this.isloading = false;
        emitAlertEvent(response.message, 'success');
        this.errorHandler.stopLoader();
      });
  }

  private redirectTo() {
    const redirectTo =
      this.route.snapshot.queryParams['returnUrl'] ?? '/gestions/monitoring';
    this.router.navigate([redirectTo]);
    return;
  }
  /**
   * Lorsque le code opt est bon
   * @param event
   */
  connect(event: Event) {
    this.isloading = true;
    event.preventDefault();

    const param = {
      moniteur_id: this.moniteurData?.id,
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
  onNPIChanges(event: OTPEvent) {
    this.npiTab = event.values;
  }

  /**
   * Renvoie le code opt
   */
  resendCode() {
    this.isloading = true;
    const param = {
      moniteur_id: this.moniteurData?.id,
      action: 'action',
    };
    this.errorHandler.startLoader('Envoie du code  ...');
    this.authService
      .resendMoniteurOTP(param)
      .pipe(
        this.errorHandler.handleServerErrors((resp) => {
          this.isloading = false;
        }, 'opt-form')
      )
      .subscribe((resp) => {
        this.errorHandler.emitSuccessAlert(
          'Un code confirmation vous a été renvoyé à nouveau dans votre boite mail'
        );
        this.isloading = false;
        this.errorHandler.stopLoader();
      });
  }
  onCaptchaResolved(event: string) {
    this.recatchaIsValid = typeof event === 'string' && event.length > 0;
  }

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

  ngAfterViewInit(): void {}
}
