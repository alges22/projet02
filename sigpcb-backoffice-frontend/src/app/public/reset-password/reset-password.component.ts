import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss'],
})
export class ResetPasswordComponent implements OnInit {
  isloading = false;
  email = '';
  password = '';
  confirm_password = '';
  userServerData: any = null;
  code = '';
  confirmedPassword = false;
  constructor(
    private authService: AuthService,
    private handler: HttpErrorHandlerService,
    private router: Router,
    private route: ActivatedRoute
  ) {}
  ngOnInit(): void {
    this.handler.startLoader();
    this.route.queryParams.subscribe((params) => {
      //Recupération du token
      this.code = params['token'];
      if (this.code) {
        this.authService
          .resetPassword({ otp: this.code })
          .pipe(this.handler.handleServerErrors())
          .subscribe((response) => {
            this.handler.stopLoader();
            this.userServerData = response.data;
          });
      }
    });
  }
  send(event: Event) {
    this.isloading = true;
    const data = {
      otp: this.code,
      password: this.password,
      confirm_password: this.confirm_password,
    };
    this.authService
      .updatePassword(data)
      .pipe(
        this.handler.handleServerErrors(
          (response) => (this.isloading = false),
          'reset-password'
        )
      )
      .subscribe((response) => {
        this.userServerData = response.data;
        this.isloading = false;
        this.handler.emitSuccessAlert('Mot de passe réinitalisé avec succès !');
        this.router.navigate(['/connexion']);
      });
  }
  confirmPassword() {
    this.confirmedPassword = this.confirm_password === this.password;
  }
}
