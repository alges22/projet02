import { FormsModule } from '@angular/forms';
import { LoginComponent } from './login/login.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { PublicRoutingModule } from './public-routing.module';
import { LogoutComponent } from './logout/logout.component';
import { PublicComponent } from './public.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import {
  RecaptchaModule,
  RECAPTCHA_SETTINGS,
  RecaptchaSettings,
  RecaptchaV3Module,
  RECAPTCHA_V3_SITE_KEY,
} from 'ng-recaptcha';
import { VueBuilderComponent } from './vue-builder/vue-builder.component';
import { CoreModule } from '../core/core.module';
import { ServicesComponent } from './services/services.component';
import { environment } from 'src/environments/environment';
import { DevenirExaminateurComponent } from './devenir-examinateur/devenir-examinateur.component';
import { SuivreDemandeComponent } from './suivre-demande/suivre-demande.component';
import { SuivieExaminateurComponent } from './suivre-demande/suivie-examinateur/suivie-examinateur.component';
import { EditDevenirExaminateurComponent } from './edit-devenir-examinateur/edit-devenir-examinateur.component';
import { EntrepriseLoginComponent } from './entreprise-login/entreprise-login.component';
import { EntrepriseLogoutComponent } from './entreprise-logout/entreprise-logout.component';
import { MoniteurLoginComponent } from './moniteur-login/moniteur-login.component';
import { DevenirMoniteurComponent } from './devenir-moniteur/devenir-moniteur.component';
import { EditDevenirMoniteurComponent } from './edit-devenir-moniteur/edit-devenir-moniteur.component';
import { SuivreMoniteurComponent } from './suivre-moniteur/suivre-moniteur.component';
import { MoniteurLogoutComponent } from './moniteur-logout/moniteur-logout.component';
const recaptchaSettings: RecaptchaSettings = {
  siteKey: environment.recaptcha_key,
};
@NgModule({
  declarations: [
    LogoutComponent,
    LoginComponent,
    PublicComponent,
    ForgotPasswordComponent,
    ResetPasswordComponent,
    VueBuilderComponent,
    ServicesComponent,
    DevenirExaminateurComponent,
    SuivreDemandeComponent,
    SuivieExaminateurComponent,
    EditDevenirExaminateurComponent,
    EntrepriseLoginComponent,
    EntrepriseLogoutComponent,
    MoniteurLoginComponent,
    DevenirMoniteurComponent,
    EditDevenirMoniteurComponent,
    SuivreMoniteurComponent,
    MoniteurLogoutComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    RecaptchaV3Module,
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
