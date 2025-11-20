import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss'],
})
export class ForgotPasswordComponent {
  isloading = false;
  email = '';
  url = environment.endpoints.fontendAdmin + '/reset-password';
  userServerData: any = null;

  constructor(
    private authService: AuthService,
    private handler: HttpErrorHandlerService,
    private router: Router
  ) {}
  send(event: Event) {
    this.isloading = true;
    this.authService
      .forgotPassword(this.email, this.url)
      .pipe(
        this.handler.handleServerError(
          'forgot-password',
          (response) => (this.isloading = false)
        )
      )
      .subscribe((response) => {
        this.isloading = false;
        this.handler.emitSuccessAlert(
          'Un lien de réinitialisation de mot de passe a été envoyé à votre adresse e-mail'
        );
        this.router.navigate(['/connexion']);
      });
  }
}
