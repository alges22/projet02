import { Component, OnInit } from '@angular/core';
import { LangChangeEvent, TranslateService } from '@ngx-translate/core';
import { Prestation } from 'src/app/core/prestation/interface/prestation';
import { PrestationService } from 'src/app/core/prestation/prestation.service';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-welcome',
  templateUrl: './welcome.component.html',
  styleUrls: ['./welcome.component.scss'],
})
export class WelcomeComponent implements OnInit {
  prestationsTemp: Prestation[] = [];
  prestations: Prestation[] = [];
  prestationTemp: any;
  prestation: any;
  permis: any;
  constructor(
    private prestationService: PrestationService,
    private translate: TranslateService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    // Obtenir les prestationsTemp depuis le service prestationService
    this.prestationsTemp = this.prestationService.getServices();
    this.permis = this.authService.storageService().get('has_permis');

    // Traduire les prestationsTemp initialement
    this.translatePrestations();

    // Souscrire aux changements de langue
    this.translate.onLangChange.subscribe(() => {
      // Traduire les prestationsTemp à chaque changement de langue
      this.translatePrestations();
    });
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

  userConnected() {
    if (this.authService.checked()) {
      return true;
    }
    return false;
  }

  demande(slug: string): void {
    this.prestationTemp = this.prestationService.getService(slug);
    if (this.prestationTemp) {
      this.translate
        .get(this.prestationTemp.title)
        .subscribe((translation: string) => {
          this.prestation = { ...this.prestationTemp, title: translation };
          $('#prestation-demande').modal('show');
        });
    }
  }
}
