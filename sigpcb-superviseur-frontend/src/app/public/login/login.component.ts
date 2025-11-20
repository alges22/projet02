import { HttpErrorHandlerService } from './../../core/services/http-error-handler.service';
import { AfterViewInit, Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import {
  AutoEcole,
  TempAutoEcole,
} from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit {
  isloading = false;

  recatchaIsValid = true;

  isValidOtpCode = false;

  form = {
    email: '',
  };

  page: 'login' | 'otp' | 'check-npi' | 'forgot-password' = 'login';

  code = null;
  userServerData: any = null;

  constructor(
    private readonly authService: AuthService,
    private readonly route: ActivatedRoute,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly router: Router //private recaptchaV3Service: ReCaptchaV3Service
  ) {}

  ngOnInit(): void {
    this.authService.storageService().destroy();
    // this.recaptchaV3Service
    //   .execute(environment.recaptcha_key)
    //   .subscribe((token) => {});
  }

  sendCode(event: Event) {
    this.isloading = true;
    event.preventDefault();

    this.errorHandler.clearServerErrorsMessages('login-form');
    this.authService
      .signin(this.form)
      .pipe(
        this.errorHandler.handleServerError('login-form', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.userServerData = response.data;
        if (!!this.userServerData.phone) {
        } else {
          this.userServerData.phone = null;
        }
        this.page = 'otp';
        this.isloading = false;
      });
  }

  private redirectTo() {
    const redirectTo =
      this.route.snapshot.queryParams['returnUrl'] ?? AuthService.REDIRECTTO;
    this.router.navigate([redirectTo]);
  }
  /**
   * Lorsque le code opt est bon
   * @param event
   */
  connect(event: Event) {
    this.isloading = true;
    event.preventDefault();

    const param = {
      user_id: this.userServerData.user_id,
      code: this.code,
      action: this.userServerData.action,
    };
    this.authService
      .opt(param)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloading = false;
        }, 'opt-form')
      )
      .subscribe((response) => {
        this.authService.attempt(response.data.access_token);
        const user = response.data.user as AutoEcole;
        this.authService.storageService().store<TempAutoEcole>('auth', {
          id: user.id,
          name: user.name,
          numero_autorisation: user.numero_autorisation,
          is_verify: user.is_verify,
        });

        this.isloading = false;
        this.errorHandler.emitSuccessAlert(response.message);
        this.redirectTo();
      });
  }
  /**
   * Renvoie le code opt
   */
  resendCode() {
    this.isloading = true;
    const param = {
      user_id: this.userServerData.user_id,
      action: this.userServerData.action,
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
          'Un code confirmation vous a été renvoyé à nouveau par SMS'
        );
        this.isloading = false;
      });
  }
  onCaptchaResolved(event: string) {
    this.recatchaIsValid = true;
  }

  /**
   * Permet da savoir si le bouton de validation est clickable
   */
  canValidate() {
    return this.recatchaIsValid && this.inputIsValid && !this.isloading;
  }

  otpCodes(event: any) {
    this.isValidOtpCode = event.isValid;
    this.code = event.values.join('');
  }

  get inputIsValid() {
    // Vérifie d'abord si l'email existe et n'est pas vide
    if (!this.form.email || this.form.email.trim().length === 0) {
      return false;
    }

    // Expression régulière pour la validation d'email
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    // Nettoie l'email et vérifie avec regex
    const cleanEmail = this.form.email.trim().toLowerCase();
    return emailRegex.test(cleanEmail);
  }
}
