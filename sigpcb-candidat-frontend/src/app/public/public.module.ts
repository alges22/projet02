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
  RecaptchaSettings,
  RecaptchaV3Module,
  RECAPTCHA_V3_SITE_KEY,
} from 'ng-recaptcha';
import { RegisterComponent } from './register/register.component';
import { VueBuilderComponent } from './vue-builder/vue-builder.component';
import { InscriptionExamenComponent } from './service-details/inscription-examen/inscription-examen.component';
import { CoreModule } from '../core/core.module';
import { PermisNumeriqueComponent } from './services/permis-numerique/permis-numerique.component';
import { ServicesComponent } from './services/services.component';
import { SuivreMonDossierComponent } from './services/suivre-mon-dossier/suivre-mon-dossier.component';
import { AuthenticitePermisComponent } from './services/authenticite-permis/authenticite-permis.component';
import { PermisInternationalComponent } from './services/permis-international/permis-international.component';
import { EchangePermisComponent } from './services/echange-permis/echange-permis.component';
import { DuplicataPermisComponent } from './services/duplicata-permis/duplicata-permis.component';
import { SuivreEserviceComponent } from './services/suivre-mon-dossier/suivre-eservice/suivre-eservice.component';
import { ProrogationPermisComponent } from './services/prorogation-permis/prorogation-permis.component';
import { environment } from 'src/environments/environment';
import { AttestationSuccessComponent } from './services/attestation-success/attestation-success.component';
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
    ServiceDetailsComponent,
    RegisterComponent,
    VueBuilderComponent,
    SuivreMonDossierComponent,
    InscriptionExamenComponent,
    PermisNumeriqueComponent,
    ServicesComponent,
    AuthenticitePermisComponent,
    PermisInternationalComponent,
    EchangePermisComponent,
    DuplicataPermisComponent,
    SuivreEserviceComponent,
    ProrogationPermisComponent,
    AttestationSuccessComponent,
    // ServicesAnattComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    // RecaptchaModule,
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
