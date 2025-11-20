import { CoreModule } from 'src/app/core/core.module';
import { AuthInterceptor } from './core/interceptor/auth.interceptor';
import { LOCALE_ID, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { LoginComponent } from './home/login/login.component';
import { WelcomeComponent } from './home/welcome/welcome.component';
import { LogoutComponent } from './home/logout/logout.component';
import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
registerLocaleData(localeFr);
@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    WelcomeComponent,
    LogoutComponent,
  ],
  imports: [BrowserModule, AppRoutingModule, HttpClientModule, CoreModule],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true,
    },
    { provide: LOCALE_ID, useValue: 'fr-FR' },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
