import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { LangChangeEvent, TranslateService } from '@ngx-translate/core';
import { ReCaptchaV3Service } from 'ng-recaptcha';
import { catchError, of } from 'rxjs';
import { AlertPosition, AlertType } from 'src/app/core/interfaces/alert';
import { AuthService } from 'src/app/core/services/auth.service';
import { BrowserEventServiceService } from 'src/app/core/services/browser-event-service.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { ServerResponseCallback } from 'src/app/types/server';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss'],
})
export class RegisterComponent {
  isloading = false;

  inputIsValid = false;
  recatchaIsValid = false;

  isValidOtpCode = false;

  form = {
    email: '',
    password: '',
  };

  page: 'register' | 'otp' | 'check-npi' | 'forgot-password' | 'confirmation' =
    'register';

  code = null;
  userServerData: any = null;

  accepted = false;
  /**
   * 1 - has phone
   * 0 - lost  phone
   * -1 - Unkown phone
   * null - Any action
   */
  hasPhone: 0 | 1 | -1 | null = null;
  npi!: string;
  userEmail: any;
  userPhone: any;
  codeMessage!: string;
  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private errorHandler: HttpErrorHandlerService,
    private router: Router,
    private translate: TranslateService,
    private browservice: BrowserEventServiceService,
    private recaptchaV3Service: ReCaptchaV3Service
  ) {}

  ngOnInit(): void {
    // Souscrire à l'événement de changement de langue
    this.translate.onLangChange.subscribe((event: LangChangeEvent) => {
      if (this.page == 'check-npi') this.translateCodeMessage(); // Mettre à jour la traduction
    });
  }

  checkNPI(event: Event) {
    // this.page = 'check-npi';
    this.isloading = true;
    event.preventDefault();

    this.authService
      .checknpi({ npi: this.npi })
      .pipe(
        this.errorHandler.handleServerErrors(
          (response: any) => {
            this.isloading = false;
            this.translate
              .get(response.message)
              .subscribe((translation: string) => {
                this.emitAlert(translation, 'danger', 'middle', true);
              });
          },
          'register',
          false
        )
      )
      .subscribe((response) => {
        this.userServerData = response.data;
        this.page = 'check-npi';
        this.isloading = false;
        this.translateCodeMessage();
      });
  }

  translateCodeMessage(): void {
    const phoneNumber = this.userServerData.telephone;
    const translationKey = 'validation.code_message';

    this.translate.get(translationKey).subscribe((translation: string) => {
      const maskedPhoneNumber = phoneNumber.replace(
        /(\d{2})\d+(\d{2})/,
        '$1****$2'
      );
      this.codeMessage = translation.replace('{telephone}', maskedPhoneNumber);
    });
  }

  private emitAlert(
    message = '',
    type: AlertType = 'warning',
    postion: AlertPosition = 'bottom-right',
    fixed = false
  ) {
    this.browservice.emitAlertEvent({
      message: message,
      type: type,
      position: postion,
      fixed: fixed,
    });
  }
  /**
   * Après quand l'utilisateur clique sur le bouton  recevoir le code
   */
  sendCode(event: Event) {
    event.preventDefault();
    this.isloading = true;
    const param = {
      npi: this.userServerData.npi,
      email: this.userServerData.email,
      phone: this.userServerData.telephone,
      lang: localStorage.getItem('lang'),
    };
    console.log(param);
    this.errorHandler.clearServerErrorsMessages('register');
    this.authService
      .signup(param)
      .pipe(
        this._handleServerError('register', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.userServerData = response.data;
        this.userEmail = param.email;
        this.userPhone = param.phone;
        this.page = 'otp';
        this.isloading = false;
      });
  }

  private _handleServerError(
    formId: string,
    callbackAction?: ServerResponseCallback,
    emitAlert = true
  ) {
    return catchError((responseError) => {
      if (formId && formId !== '') {
        this.browservice.emitErrorsEvent(formId, responseError.error);
      }
      if (callbackAction) {
        callbackAction(responseError.error, formId);
      }
      const error = responseError.error;
      if (!error.status) {
        if (emitAlert) {
          let message = '';
          let entete = error.message;
          this.translate.get(entete).subscribe((translation: string) => {
            entete = translation;
          });
          //Au cas ou des erreurs seront présentes dans l'objet message
          if (typeof error.errors === 'object' && error.errors !== undefined) {
            for (const k in error.errors) {
              let sm = '';
              if (Object.prototype.hasOwnProperty.call(error.errors, k)) {
                const err = error.errors[k];
                if (Array.isArray(err)) {
                  sm = err
                    .map((mes) => {
                      this.translate
                        .get(mes)
                        .subscribe((translation: string) => {
                          mes = translation;
                        });
                      return `<li>${mes}</li>`;
                    })
                    .join(' ');
                } else if (typeof err === 'string') {
                  sm = sm.concat(`<li>${err}</li>`);
                }
              }
              message = message.concat(sm);
            }
            const messageFormat = `<b>${entete}</b><ul class="text-danger mx-3 text-start mt-3">${message}</ul>`;

            message = messageFormat;
          } else {
            message = entete;
          }
          this.emitAlert(message, 'danger', 'middle', true);
          this.browservice.hideLoader();
        }
        throw new Error('Server error');
      }
      return of(responseError.error);
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
    this.isloading = true;
    event.preventDefault();

    // this.page = 'confirmation';
    const param = {
      user_id: this.userServerData.user_id,
      code: this.code,
      action: this.userServerData.action,
    };
    this.authService
      .opt(param)
      .pipe(
        this._handleServerError('confirmation', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((response) => {
        this.authService.attempt(response.data.access_token);
        this.authService.storageService().store('auth', {
          id: response.data.user.id,
          npi: response.data.user.npi,
          has_dossier_permis: response.data.user.has_dossier_permi,
        });

        // this.authService.storageService().store('userRoles',response.data.user.roles);
        // this.authService.storageService().store('userPermissions', [
        //   {
        //     id: 0,
        //     name: 'r-admin',
        //   },
        //   {
        //     id: 1,
        //     name: 'cu-admin',
        //   },
        //   {
        //     id: 2,
        //     name: 'd-admin',
        //   },
        // ]);
        this.isloading = false;
        this.errorHandler.emitSuccessAlert(response.message);
        this.redirectTo();
        return;
      });
  }
  /**
   * Renvoie le code opt
   */
  resendCode(event: Event) {
    this.isloading = true;
    event.preventDefault();
    const param = {
      user_id: this.userServerData.user_id,
      email: this.userEmail,
      phone: this.userPhone,
      action: 'register',
      lang: localStorage.getItem('lang'),
    };
    console.log(param);
    this.authService
      .resendOpt(param)
      .pipe(
        this._handleServerError('register', (response) => {
          this.isloading = false;
        })
      )
      .subscribe((resp) => {
        let message = '';
        const translateKey =
          'Un code confirmation vous a été renvoyé à nouveau dans votre boite mail';
        this.translate.get(translateKey).subscribe((translation: string) => {
          message = translation;
        });
        this.errorHandler.emitSuccessAlert(message);
        this.isloading = false;
      });
  }
  onCaptchaResolved(event: string) {
    if (event) {
      this.recatchaIsValid = true;
    } else {
      this.recatchaIsValid = false;
    }
  }
  onInputValid(event: { isValid: boolean; values: (number | null)[] }) {
    this.inputIsValid = event.isValid;

    if (this.inputIsValid) {
      this.npi = event.values.join('');
    }
  }

  /**
   * Permet da savoir si le bouton de validation est clickable
   */
  canValidate() {
    return this.inputIsValid && this.accepted;
  }

  onSelectHasPhone() {
    console.log(this.hasPhone);
  }

  otpCodes(event: any) {
    this.isValidOtpCode = event.isValid;
    if (this.isValidOtpCode) {
      this.code = event.values.join('');
    }
  }
  iAccepted(event: any) {
    this.accepted = event.target.checked;
  }
}
