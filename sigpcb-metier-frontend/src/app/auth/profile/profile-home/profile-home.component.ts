import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-profile-home',
  templateUrl: './profile-home.component.html',
  styleUrls: ['./profile-home.component.scss'],
})
export class ProfileHomeComponent implements OnInit {
  user: any;

  passwords = {
    old_password: '',
    new_password: '',
    confirm_password: '',
  };
  onLoading = false;
  constructor(
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit() {
    this.getProfile();
  }

  private getProfile() {
    this.errorHandler.startLoader();
    return this.authService
      .profile()
      .pipe(this.errorHandler.handleServerErrors())
      .subscribe((response) => {
        this.user = response.data;

        this.errorHandler.stopLoader();
      });
  }

  resetPassword(event: Event) {
    event.preventDefault();
    this.onLoading = true;

    this.authService
      .updateNewPassword(this.passwords)
      .pipe(
        this.errorHandler.handleServerError('reset-password-form', () => {
          this.onLoading = false;
          this.passwords = {} as any;
        })
      )
      .subscribe((response) => {
        emitAlertEvent(response.message, 'success');
        this.passwords = {} as any;
        this.onLoading = false;
      });
  }
}
