import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AuthMoniteurService } from 'src/app/core/services/auth-moniteur.service';

@Component({
  selector: 'app-static-moniteur',
  templateUrl: './static-moniteur.component.html',
  styleUrls: ['./static-moniteur.component.scss'],
})
export class StaticMoniteurComponent {
  lang: any;
  constructor(
    public translate: TranslateService,
    private authMoniteurService: AuthMoniteurService
  ) {
    translate.addLangs(['en', 'fr']);
    translate.setDefaultLang('fr');
    // const browserLang = translate.getBrowserLang();
    const browserLang = localStorage.getItem('lang');

    this.lang = browserLang;

    // translate.use(browserLang.match(/en|fr/) ? browserLang : 'en');
    translate.use(browserLang ? browserLang : 'fr');
  }
  public langs = [
    {
      id: 'fr',
      name: 'FR',
    },
    {
      id: 'en',
      name: 'EN',
    },
  ];

  languageChange(lang: any) {
    this.translate.use(lang);
    localStorage.setItem('lang', lang);
  }

  userConnected() {
    if (this.authMoniteurService.checked()) {
      return true;
    }
    return false;
  }
}
