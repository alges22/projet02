import { CoreModule } from './../core/core.module';
import { FormsModule } from '@angular/forms';
import { LoginComponent } from './login/login.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { PublicRoutingModule } from './public-routing.module';
import { LogoutComponent } from './logout/logout.component';
import { PublicComponent } from './public.component';
import {
  RecaptchaModule,
  RECAPTCHA_SETTINGS,
  RecaptchaSettings,
  RecaptchaV3Module,
  RECAPTCHA_V3_SITE_KEY,
} from 'ng-recaptcha';
import { environment } from 'src/environments/environment';
const recaptchaSettings: RecaptchaSettings = {
  siteKey: environment.recaptcha_key,
};
@NgModule({
  declarations: [LogoutComponent, LoginComponent, PublicComponent],
  imports: [
    CommonModule,
    FormsModule,
    RecaptchaV3Module,
    // RecaptchaModule,
    PublicRoutingModule,
    CoreModule,
  ],
  providers: [
    {
      provide: RECAPTCHA_V3_SITE_KEY,
      useValue: recaptchaSettings.siteKey,
    },
  ],
})
export class PublicModule {}
