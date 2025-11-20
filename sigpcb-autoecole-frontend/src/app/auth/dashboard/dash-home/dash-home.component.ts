import { Component, OnInit } from '@angular/core';
import {
  Ae,
  AutoEcole,
  Moniteur,
  Promoteur,
} from 'src/app/core/interfaces/user.interface';
import { Prestation } from 'src/app/core/prestation/interface/prestation';
import { PrestationService } from 'src/app/core/prestation/prestation.service';
import { AeService } from 'src/app/core/services/ae.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { dateCounter } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-dash-home',
  templateUrl: './dash-home.component.html',
  styleUrls: ['./dash-home.component.scss'],
})
export class DashHomeComponent implements OnInit {
  prestations: Prestation[] = [];
  auth: Promoteur | Moniteur | null = null;
  aes: AutoEcole[] = [];
  ready = false;
  currentAe: Ae | null = null;
  expireLicence: number | null = null;
  licenceTextColor = 'muted';
  constructor(
    private prestationService: PrestationService,
    private storage: StorageService,
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService,
    private aeService: AeService
  ) {}
  ngOnInit(): void {
    this.prestations = this.prestationService.getServices();
    this.auth = this.storage.get('auth');

    this.errorHandler.startLoader();
    this.authService
      .profile()
      .pipe(
        this.errorHandler.handleServerErrors((response) => {
          this.ready = true;
        })
      )
      .subscribe((response) => {
        this.aes = response.data.auto_ecoles || [];
        this.aeService.setAes(this.aes);
        this.errorHandler.stopLoader();
        this.ready = true;
        this.currentAe = this.aeService.getAe();
        if (!this.currentAe) {
          this.currentAe = this.storage.get('ae');
        }
        if (this.currentAe) {
          this.expireLicence = dateCounter(this.currentAe.endLicence);
          if (this.expireLicence < 10) {
            this.licenceTextColor = 'danger';
          } else if (this.expireLicence < 30) {
            this.licenceTextColor = 'warning';
          } else {
            this.licenceTextColor = 'success';
          }
        }
      });
  }
}
