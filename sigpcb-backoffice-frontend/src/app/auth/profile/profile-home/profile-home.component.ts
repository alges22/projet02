import { Component, OnInit } from '@angular/core';
import { User } from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { UserAccessService } from 'src/app/core/services/user-access.service';
import { emitAlertEvent } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-profile-home',
  templateUrl: './profile-home.component.html',
  styleUrls: ['./profile-home.component.scss'],
})
export class ProfileHomeComponent implements OnInit {
  user: User | null = null;

  passwords = {
    old_password: '',
    new_password: '',
    confirm_password: '',
  };
  onLoading = false;

  permissions: any[] = [];
  constructor(
    private readonly authService: AuthService,
    private readonly errorHandler: HttpErrorHandlerService,
    private readonly accessService: UserAccessService
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
        const roles = this.user?.roles ?? [];
        for (const role of roles) {
          const permissions = role.permissions;
          for (const permission of permissions) {
            this.permissions.push(permission);
          }
        }

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
