import { Component, Input } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-blue-aside',
  templateUrl: './blue-aside.component.html',
  styleUrls: ['./blue-aside.component.scss'],
})
export class BlueAsideComponent {
  lang: any;
  @Input('auth') auth = null;
  constructor(public translate: TranslateService) {
    translate.addLangs(['en', 'fr']);
    translate.setDefaultLang('fr');
    // const browserLang = translate.getBrowserLang();
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
}
