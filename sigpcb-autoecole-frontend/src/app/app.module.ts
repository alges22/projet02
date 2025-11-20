import { CoreModule } from 'src/app/core/core.module';
import { AuthInterceptor } from './core/interceptor/auth.interceptor';
import * as $ from 'jquery';

import { LOCALE_ID, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { NgChartsModule } from 'ng2-charts';
import { PublicModule } from './public/public.module';
import { CookieModule } from 'ngx-cookie';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';

import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
import { RECAPTCHA_V3_SITE_KEY } from 'ng-recaptcha';
import { environment } from 'src/environments/environment';
import * as bootstrap from 'bootstrap';

// ...

registerLocaleData(localeFr);
@NgModule({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    CookieModule.withOptions(),
    NgChartsModule,
    PublicModule,
    CoreModule,
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true,
    },
    { provide: LOCALE_ID, useValue: 'fr-FR' },
    { provide: RECAPTCHA_V3_SITE_KEY, useValue: environment.recaptcha_key },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
