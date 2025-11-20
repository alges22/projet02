import { HttpErrorHandlerService } from './../../core/services/http-error-handler.service';
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from 'src/app/core/services/auth.service';
@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit {
  hidepassword = true;
  isloading = false;

  emailErrors =
    "L'adresse e-mail est requise | L'adresse email invalide | Adresse e-mail trop long";
  passwordErrors =
    'Le mote de passe est requis | Mot de passe trop court | Mot de passe trop long';

  form = {
    email: '',
  };

  page: 'login' | 'otp' | 'forgot-password' = 'login';

  code = null;
  userServerData: any = null;
  constructor(
    private readonly authService: AuthService,
    private readonly route: ActivatedRoute,
    private readonly errorHandler: HttpErrorHandlerService,
    //  private recaptchaV3Service: ReCaptchaV3Service,
    private readonly router: Router
  ) {}

  ngOnInit(): void {
    // this.recaptchaV3Service
    //   .execute(environment.recaptcha_key)
    //   .subscribe((token) => {});
  }

  togglePassword(): void {
    this.hidepassword = !this.hidepassword;
  }

  onSubmit(event: Event) {
    this.isloading = true;
    event.preventDefault();

    this.errorHandler.clearServerErrorsMessages('login');
    this.authService
      .signin(this.form)
      .pipe(
        this.errorHandler.handleServerError('login', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.userServerData = response.data;
        this.page = 'otp';
        this.isloading = false;
      });
  }

  private redirectTo() {
    const redirectTo =
      this.route.snapshot.queryParams['returnUrl'] ?? AuthService.REDIRECTTO;
    this.router.navigate([redirectTo]);
    return;
  }
  /**
   * Lorsque le code opt est bon
   * @param event
   */
  connect(event: Event) {
    event.preventDefault();
    this.afterToken();
    // this.recaptchaV3Service
    //   .execute('importantAction')
    //   .subscribe((token) => this.afterToken(token));
  }

  afterToken(token?: string) {
    this.isloading = true;

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
        this.authService.storageService().store('auth', {
          id: response.data.user.id,
          first_name: response.data.user.first_name,
          last_name: response.data.user.last_name,
        });

        this.isloading = false;

        this.errorHandler.emitSuccessAlert(response.message);
        this.redirectTo();
        return;
      });
  }

  getOtp(event: any) {
    this.code = event.values.join('');
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
          'Un code confirmation vous a été renvoyé à nouveau dans votre boite mail'
        );
        this.isloading = false;
      });
  }

  onEnter(event: any) {
    if (event.code == 'Enter' || event.charCode == 13) {
      this.onSubmit(event);
    }
  }
}
