import {
  AfterViewInit,
  Component,
  ElementRef,
  ViewChild,
  NgZone,
} from '@angular/core';
import { Router } from '@angular/router';
import { catchError, of } from 'rxjs';
import { ProfileData } from 'src/app/core/interfaces/profiles';
import { AuthService } from 'src/app/core/services/auth.service';
import { ProfileService } from 'src/app/core/services/profile.service';
import { StateService } from 'src/app/core/services/state.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { ServerResponseType } from 'src/app/core/types/server-response.type';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements AfterViewInit {
  @ViewChild('loginSuccess') loginSuccess: ElementRef<HTMLElement> | null =
    null;

  modalElement: HTMLElement | null = null;
  codeErrorMessage: string | null = null;

  lengths = {
    code: 12,
  };
  data = {
    code: '',
  };
  profileData: ProfileData | null = null;
  onVerification = false;
  try = 0;

  scann = true;
  alertMessage: string | null = null;
  constructor(
    private profileService: ProfileService,
    private authService: AuthService,
    private storage: StorageService,
    private router: Router,
    private ngZone: NgZone,
    private stateService: StateService
  ) {}

  gotoComposition() {
    this.stateService.enterFullScreen();
    setTimeout(() => {
      this.router.navigate([AuthService.REDIRECTTO]);
    }, 1300);
  }

  onInputCode(event: Event) {
    const target = event.target as HTMLInputElement;
    const values = target.value.split('');

    if (values.some((value) => isNaN(Number(value)))) {
      this.codeErrorMessage = 'Le code saisi est invalide';
    } else {
      this.codeErrorMessage = null;
      const diff = this.lengths.code - target.value.length;
      this.data.code = target.value;
      if (diff < 0) {
        this.codeErrorMessage = 'Le code saisi est invalide';
      } else {
        if (diff === 0) {
          this.onVerification = true;
          this.observeDanConnect();
        }
      }
    }
  }
  ngAfterViewInit(): void {
    if (this.loginSuccess) {
      this.modalElement = this.loginSuccess.nativeElement;
    }
  }
  observeDanConnect() {
    if (this.try < 2) {
      this.onVerification = true;
      this.try++;
      this.authService
        .signin(this.data)
        .pipe(
          catchError((e: any) => {
            this.scann = false;
            const error = e.error as ServerResponseType;
            this.alertMessage =
              error.message || 'Un problème inatttendu est survenu';
            this.onVerification = false;
            return of(e.error);
          })
        )
        .subscribe((response) => {
          //On éteint le chargement
          this.onVerification = true;

          if (response.status) {
            this.data.code = '';
            this.modalElement?.classList.remove('hide-modal');
            const profileData: ProfileData = response.data.profile_data;

            if (profileData) {
              this.profileData = profileData;
              this.profileData.access_token = response.data.access_token;
              this.profileService.set(profileData);
              this.storage.store('access_token', response.data.access_token);
              this.gotoComposition();
            }
          } else {
            this.alertMessage = response.message;
          }
        });
    }
  }

  scan(code: string): void {
    this.ngZone.run(() => {
      this.data.code = code;
      this.onVerification = true;
      this.scann = true;
      this.observeDanConnect();
    });
  }

  restart() {
    this.try = 0;
    this.onVerification = false;
    this.alertMessage = null;
    this.scann = true;
    window.location.href = '';
  }
}
