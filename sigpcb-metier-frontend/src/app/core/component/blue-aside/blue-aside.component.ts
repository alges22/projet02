import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '../../services/auth.service';
import { redirectTo } from 'src/app/helpers/helpers';

@Component({
  selector: 'app-blue-aside',
  templateUrl: './blue-aside.component.html',
  styleUrls: ['./blue-aside.component.scss'],
})
export class BlueAsideComponent {
  lang: any;
  constructor(
    public translate: TranslateService,
    private authService: AuthService
  ) {
    translate.addLangs(['en', 'fr']);
    translate.setDefaultLang('fr');
    // const browserLang = translate.getBrowserLang();
    if (!localStorage.getItem('lang')) localStorage.setItem('lang', 'fr');
    const browserLang = localStorage.getItem('lang');

    this.lang = browserLang;

    // translate.use(browserLang.match(/en|fr/) ? browserLang : 'en');
    translate.use(browserLang ? browserLang : 'fr');
  }

  ngOnInit(): void {}
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
  goto(path: any) {
    redirectTo(path, 0);
  }
}
