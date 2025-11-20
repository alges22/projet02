import { Component, OnInit } from '@angular/core';
import { LangChangeEvent, TranslateService } from '@ngx-translate/core';
import {
  Ae,
  AutoEcole,
  Moniteur,
  Promoteur,
} from 'src/app/core/interfaces/user.interface';
import { Prestation } from 'src/app/core/prestation/interface/prestation';
import { PrestationService } from 'src/app/core/prestation/prestation.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
import { StorageService } from 'src/app/core/services/storage.service';

@Component({
  selector: 'app-welcome',
  templateUrl: './welcome.component.html',
  styleUrls: ['./welcome.component.scss'],
})
export class WelcomeComponent implements OnInit {
  prestations: Prestation[] = [];
  auth: Promoteur | Moniteur | null = null;
  aes: AutoEcole[] = [];
  ready = false;
  aeSelected: number | null = null;
  constructor(
    private prestationService: PrestationService,
    private storage: StorageService,
    private errorHandler: HttpErrorHandlerService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.auth = this.storage.get('auth');
    // Obtenir les prestationsTemp depuis le service prestationService
    this.prestations = this.prestationService.getServices();

    if (this.auth) {
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
          this.errorHandler.stopLoader();
          this.ready = true;
          if (this.aes.length == 1) {
            this.aeSelected = this.aes[0].id;
          }
        });
    }
  }
}
