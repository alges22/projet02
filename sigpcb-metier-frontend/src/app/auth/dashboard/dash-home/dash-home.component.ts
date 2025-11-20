import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Prestation } from 'src/app/core/prestation/interface/prestation';
import { PrestationService } from 'src/app/core/prestation/prestation.service';
import { AuthService } from 'src/app/core/services/auth.service';
import { HttpErrorHandlerService } from 'src/app/core/services/http-error-handler.service';
type Page = 'user-type' | 'todo-action' | 'prepare-my-self';
@Component({
  selector: 'app-dash-home',
  templateUrl: './dash-home.component.html',
  styleUrls: ['./dash-home.component.scss'],
})
export class DashHomeComponent implements OnInit {
  prestations: Prestation[] = [];
  prestation: any;
  prestationTemp: any;
  prestationsTemp: Prestation[] = [];
  user: any;
  permis: any;
  page: Page = 'user-type';
  constructor(
    private prestationService: PrestationService,
    private translate: TranslateService,
    private authService: AuthService,
    private errorHandler: HttpErrorHandlerService
  ) {}
  ngOnInit(): void {
    // this.user = this.authService.storageService().get('auth');
    // Obtenir les prestationsTemp depuis le service prestationService
    this.prestationsTemp = this.prestationService.getServices();

    // Traduire les prestationsTemp initialement
    this.translatePrestations();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePrestations();
    });

    this._getCandidatWithNpi();
  }

  translatePrestations(): void {
    const translationPromises = this.prestationsTemp.map(
      (prestation: Prestation) => {
        return this.translate
          .get(prestation.title)
          .toPromise()
          .then((translation: string) => {
            return { ...prestation, title: translation };
          });
      }
    );

    Promise.all(translationPromises).then(
      (translatedPrestations: Prestation[]) => {
        // Mettre à jour les prestationsTemp traduites
        this.prestations = translatedPrestations;
      }
    );
  }

  private _getCandidatWithNpi() {
    var user: any = this.authService.storageService().get('auth');
    this.permis = this.authService.storageService().get('permis');
    if (user) {
      this.errorHandler.startLoader();
      this.authService
        .checknpi({ npi: user.npi })
        .pipe(this.errorHandler.handleServerErrors())
        .subscribe((response) => {
          this.user = response.data;
          this.errorHandler.stopLoader();
        });
    }
  }

  demande(slug: string): void {
    this.prestationTemp = this.prestationService.getService(slug);
    if (this.prestationTemp) {
      this.translate
        .get(this.prestationTemp.title)
        .subscribe((translation: string) => {
          this.prestation = { ...this.prestationTemp, title: translation };
          this.page = 'user-type';
          $('#prestation-demande').modal('show');
          this.prestationService.emitModalOpenedEvent();
        });
    }
  }
}
