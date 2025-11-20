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
    RecaptchaModule,
    PublicRoutingModule,
    CoreModule,
    RecaptchaV3Module,
  ],
  providers: [
    {
      provide: RECAPTCHA_SETTINGS,
      useValue: recaptchaSettings,
    },
  ],
})
export class PublicModule {}
