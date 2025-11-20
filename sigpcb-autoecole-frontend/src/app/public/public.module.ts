import { CoreModule } from './../core/core.module';
import { FormsModule } from '@angular/forms';
import { LoginComponent } from './login/login.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { PublicRoutingModule } from './public-routing.module';
import { LogoutComponent } from './logout/logout.component';
import { PublicComponent } from './public.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { ServiceDetailsComponent } from './service-details/service-details.component';
import {
  RecaptchaModule,
  RECAPTCHA_SETTINGS,
  RecaptchaSettings,
  RecaptchaV3Module,
} from 'ng-recaptcha';
import { RegisterComponent } from './register/register.component';
import { VueBuilderComponent } from './vue-builder/vue-builder.component';
import { ConfirmAccountComponent } from './confirm-accoount/confirm-account.component';
import { environment } from 'src/environments/environment';
import { DemandeAgrementComponent } from './demande-agrement/demande-agrement.component';
import { MoniteurLoginComponent } from './moniteur-login/moniteur-login.component';

@NgModule({
  declarations: [
    LogoutComponent,
    LoginComponent,
    PublicComponent,
    ForgotPasswordComponent,
    ResetPasswordComponent,
    ServiceDetailsComponent,
    RegisterComponent,
    VueBuilderComponent,
    ConfirmAccountComponent,
    DemandeAgrementComponent,
    MoniteurLoginComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    RecaptchaModule,
    PublicRoutingModule,
    CoreModule,
    RecaptchaV3Module,
  ],
})
export class PublicModule {}
