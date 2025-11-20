import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthModule } from 'src/app/auth/auth.module';
import {
  AutoEcole,
  TempAutoEcole,
} from 'src/app/core/interfaces/user.interface';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';

@Component({
  selector: 'app-confirm-account',
  templateUrl: './confirm-account.component.html',
  styleUrls: ['./confirm-account.component.scss'],
})
export class ConfirmAccountComponent implements OnInit {
  token: string | null = null; // Get from the URL
  confirmationError: string | null = null;
  verified: boolean | null = null;

  errorMessage = '';
  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router,
    private errorHandler: HttpErrorHandlerService
  ) {}

  ngOnInit() {
    this.token = this.route.snapshot.queryParamMap.get('token');
    if (this.token) {
      this.confirmAccount();
    } else {
      this.errorHandler.emitDangerAlert(
        'Token invalid ou expiré',
        'danger',
        'middle',
        true
      );
    }
  }

  confirmAccount() {
    this.authService
      .confirmAccount(this.token ?? '')
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.verified = false;
          this.errorMessage = response.message || this.errorMessage;
        })
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
        // Redirect to the login page or any other appropriate page
        this.router.navigate([AuthModule.home]);
        this.verified = true;
      });
  }
}
