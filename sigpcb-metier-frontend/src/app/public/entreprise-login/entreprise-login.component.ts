import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-entreprise-login',
  templateUrl: './entreprise-login.component.html',
  styleUrls: ['./entreprise-login.component.scss'],
})
export class EntrepriseLoginComponent {
  hidepassword = true;
  isloading = false;

  emailErrors =
    "L'adresse e-mail est requise | L'adresse email invalide | Adresse e-mail trop long";
  passwordErrors =
    'Le mote de passe est requis | Mot de passe trop court | Mot de passe trop long';

  form = {
    email: '',
    password: '',
  };

  page: 'login' | 'otp' | 'forgot-password' = 'login';

  code = null;
  userServerData: any = null;
  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private errorHandler: HttpErrorHandlerService,
    private router: Router,
    private recaptchaV3Service: ReCaptchaV3Service
  ) {}
  ngOnInit(): void {
    this.recaptchaV3Service
      .execute(environment.recaptcha_key)
      .subscribe((token) => {});
  }

  togglePassword(): void {
    this.hidepassword = !this.hidepassword;
  }

  onSubmit(event: Event) {
    this.isloading = true;
    event.preventDefault();

    this.errorHandler.clearServerErrorsMessages('login');
    this.authService
      .signinEntreprise(this.form)
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
      this.route.snapshot.queryParams['returnUrl'] ??
      AuthService.REDIRECTENTREPRISETO;
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
      user_id: this.userServerData.user_id,
      code: this.code,
      action: this.userServerData.action,
    };
    this.authService
      .optEntreprise(param)
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.isloading = false;
        }, 'opt-form')
      )
      .subscribe((response) => {
        this.authService.attempt(response.data.access_token);
        this.authService.storageService().store('auth-entreprise', {
          id: response.data.entreprise.id,
          name: response.data.entreprise.name,
          email: response.data.entreprise.email,
        });

        this.isloading = false;

        this.errorHandler.emitSuccessAlert(response.message);
        this.redirectTo();
        return;
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
      .resendOptEntreprise(param)
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
}
