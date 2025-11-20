import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from 'src/app/core/services/auth.service';

@Component({
  selector: 'app-static-module',
  templateUrl: './static.component.html',
  styleUrls: ['./static.component.scss'],
})
export class StaticComponent {
  lang: any;
  constructor(
    public translate: TranslateService,
    private authService: AuthService
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
    if (this.authService.checked()) {
      return true;
    }
    return false;
  }
}
